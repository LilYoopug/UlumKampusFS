import React, { useState, useMemo, useEffect } from 'react';
import { ALL_USERS } from '@/constants';
import { DiscussionThread, DiscussionPost, User } from '@/types';
import { useLanguage } from '@/contexts/LanguageContext';
import { Icon } from '@/src/ui/components/Icon';
import { timeAgo } from '@/utils/time';
import { discussionThreadAPI, discussionPostAPI } from '@/services/apiService';
import { AxiosError } from 'axios';

interface DiscussionForumProps {
    courseId: string;
    currentUser: User;
    initialThreadId?: string;
}

export const DiscussionForum: React.FC<DiscussionForumProps> = ({ courseId, currentUser, initialThreadId }) => {
    const { t } = useLanguage();
    const [threads, setThreads] = useState<DiscussionThread[]>([]);
    const [selectedThread, setSelectedThread] = useState<DiscussionThread | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [searchTerm, setSearchTerm] = useState('');
    const [showNewThreadForm, setShowNewThreadForm] = useState(false);
    const [newThreadTitle, setNewThreadTitle] = useState('');
    const [newThreadContent, setNewThreadContent] = useState('');
    const [replyContent, setReplyContent] = useState('');
    const [posting, setPosting] = useState(false);

    // Fetch threads when component mounts or courseId changes
    useEffect(() => {
        fetchThreads();
    }, [courseId]);

    // Select initial thread if provided
    useEffect(() => {
        if (initialThreadId && threads.length > 0) {
            const threadToSelect = threads.find(t => t.id === initialThreadId);
            if (threadToSelect) {
                setSelectedThread(threadToSelect);
            }
        }
    }, [initialThreadId, threads]);

    // Fetch posts when a thread is selected
    useEffect(() => {
        if (selectedThread) {
            fetchPosts(selectedThread.id);
        }
    }, [selectedThread?.id]);

    const fetchThreads = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = await discussionThreadAPI.getByCourse(courseId);
            // Backend wraps data in { success, message, data } structure
            const responseData = response.data as any;
            const threadsData = responseData.data || responseData;
            
            // Check if it's an array
            if (!Array.isArray(threadsData)) {
                console.error('Threads data is not an array:', threadsData);
                setError('Format data tidak valid');
                return;
            }
            
            // Transform backend data to frontend format
            const transformedThreads = threadsData.map((thread: any) => ({
                id: String(thread.id),
                courseId: String(thread.course_id),
                title: thread.title,
                authorId: String(thread.created_by),
                createdAt: thread.created_at,
                isPinned: thread.is_pinned,
                isClosed: thread.status === 'closed' || thread.is_closed,
                posts: [] // Posts will be fetched separately
            }));
            setThreads(transformedThreads);
        } catch (err) {
            console.error('Error fetching threads:', err);
            setError('Gagal memuat diskusi');
        } finally {
            setLoading(false);
        }
    };

    const fetchPosts = async (threadId: string) => {
        try {
            const response = await discussionPostAPI.getByThread(threadId);
            // Backend wraps data in { success, message, data } structure
            const responseData = response.data as any;
            const postsData = responseData.data || responseData;
            
            // Check if it's an array
            if (!Array.isArray(postsData)) {
                console.error('Posts data is not an array:', postsData);
                return;
            }
            
            // Transform backend posts to frontend format
            // Backend includes user data via eager loading: with(['user', 'parent', 'replies.user'])
            const transformedPosts = postsData.map((post: any) => ({
                id: String(post.id),
                authorId: String(post.user_id),
                createdAt: post.created_at,
                content: post.content,
                isSolution: post.is_solution,
                // Include user data from the response
                // Note: Backend returns 'avatar' field, which maps to avatarUrl accessor
                authorName: post.user?.name || 'Pengguna tidak dikenal',
                authorAvatar: post.user?.avatar || post.user?.avatar_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(post.user?.name || 'User'),
                authorRole: post.user?.role || 'student'
            })).filter((post, index, self) => {
                // Remove duplicates based on content and authorId
                // Keep only the first occurrence (highest ID, which is likely the most recent)
                const isDuplicate = self.findIndex(
                    p => p.content === post.content && p.authorId === post.authorId
                ) !== index;
                return !isDuplicate;
            }).sort((a, b) => {
                // Sort by creation time (oldest first for chronological order)
                return new Date(a.createdAt).getTime() - new Date(b.createdAt).getTime();
            });

            setThreads(prev => prev.map(thread => 
                thread.id === threadId 
                    ? { ...thread, posts: transformedPosts }
                    : thread
            ));
            
            setSelectedThread(prev => prev ? 
                { ...prev, posts: transformedPosts } 
                : null);
        } catch (err) {
            console.error('Error fetching posts:', err);
        }
    };

    const filteredThreads = useMemo(() => {
        return threads
            .filter(thread => thread.title.toLowerCase().includes(searchTerm.toLowerCase()))
            .sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime())
            .sort((a, b) => (a.isPinned === b.isPinned) ? 0 : a.isPinned ? -1 : 1);
    }, [threads, searchTerm]);

    const handleSelectThread = (thread: DiscussionThread) => {
        setSelectedThread(thread);
        setShowNewThreadForm(false);
    };

    const handleCreateThread = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!newThreadTitle.trim() || !newThreadContent.trim()) return;
        
        setPosting(true);
        try {
            // Create thread with initial post
            const response = await discussionThreadAPI.create({
                courseId: courseId,
                title: newThreadTitle,
                content: newThreadContent, // This will be the first post
                type: 'question'
            } as any); // Cast to any because backend expects different structure

            const backendData = response.data as any;
            const newThread = {
                id: String(backendData.id),
                courseId: String(backendData.course_id),
                title: backendData.title,
                authorId: String(backendData.created_by),
                createdAt: backendData.created_at,
                isPinned: backendData.is_pinned,
                isClosed: backendData.status === 'closed' || backendData.is_closed,
                posts: [{
                    id: String(backendData.id), // First post will be fetched separately
                    authorId: String(backendData.created_by),
                    createdAt: backendData.created_at,
                    content: newThreadContent,
                }]
            };

            setThreads(prev => [newThread, ...prev]);
            setSelectedThread(newThread);
            setShowNewThreadForm(false);
            setNewThreadTitle('');
            setNewThreadContent('');
        } catch (err) {
            console.error('Error creating thread:', err);
            setError('Gagal membuat diskusi');
        } finally {
            setPosting(false);
        }
    };

    const handlePostReply = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!replyContent.trim() || !selectedThread || selectedThread.isClosed) return;

        setPosting(true);
        try {
            const response = await discussionPostAPI.createPost(selectedThread.id, {
                content: replyContent
            });

            const newPost: DiscussionPost = {
                id: String(response.data.id),
                authorId: String(response.data.user_id),
                createdAt: response.data.created_at,
                content: response.data.content,
            };

            const updatedThreads = threads.map(thread => {
                if (thread.id === selectedThread.id) {
                    // Add new post and sort by creation time (oldest first)
                    const updatedPosts = [...thread.posts, newPost].sort((a, b) => {
                        return new Date(a.createdAt).getTime() - new Date(b.createdAt).getTime();
                    });
                    return { ...thread, posts: updatedPosts };
                }
                return thread;
            });

            setThreads(updatedThreads);
            setSelectedThread(prev => {
                if (!prev) return null;
                const updatedPosts = [...prev.posts, newPost].sort((a, b) => {
                    return new Date(a.createdAt).getTime() - new Date(b.createdAt).getTime();
                });
                return { ...prev, posts: updatedPosts };
            });
            setReplyContent('');
        } catch (err) {
            console.error('Error posting reply:', err);
            setError('Gagal mengirim balasan');
        } finally {
            setPosting(false);
        }
    };

    const getUser = (authorId: string, authorName?: string, authorAvatar?: string, authorRole?: string): User => {
        // First try to find user in ALL_USERS by studentId
        const user = ALL_USERS.find(u => u.studentId === authorId);
        if (user) return user;
        
        // If not found, use the provided data from API response
        return { 
            name: authorName || 'Pengguna tidak dikenal', 
            avatarUrl: authorAvatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(authorName || 'User'),
            role: mapBackendRoleToFrontend(authorRole || 'student'),
            studentId: authorId,
            email: '',
            joinDate: '',
            bio: ''
        };
    };

    // Helper to map backend role to frontend role
    const mapBackendRoleToFrontend = (backendRole: string): User['role'] => {
        const roleMap: Record<string, User['role']> = {
            'student': 'Mahasiswa',
            'dosen': 'Dosen',
            'lecturer': 'Dosen',
            'admin': 'Manajemen Kampus',
            'prodi_admin': 'Prodi Admin',
            'super_admin': 'Super Admin',
            'maba': 'MABA',
            'MABA': 'MABA',
        };
        return roleMap[backendRole] || 'Mahasiswa';
    };

    return (
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[70vh] overflow-hidden">
            {/* Left Column: Thread List */}
            <div className="lg:col-span-1 bg-slate-50 dark:bg-slate-900/50 rounded-lg p-4 flex flex-col border border-slate-200 dark:border-slate-700">
                <div className="relative mb-2">
                    <Icon className="absolute start-3 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></Icon>
                    <input type="text" placeholder="Cari utas..." value={searchTerm} onChange={e => setSearchTerm(e.target.value)} className="w-full ps-10 pe-4 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-60 text-slate-800 dark:text-white"/>
                </div>
                <button onClick={() => { setShowNewThreadForm(true); setSelectedThread(null); }} className="w-full my-2 px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700">
                    Mulai Utas Baru
                </button>
                <div className="flex-1 overflow-y-auto">
                    {loading ? (
                        <div className="flex items-center justify-center h-full text-slate-500">
                            <p>Memuat diskusi...</p>
                        </div>
                    ) : error ? (
                        <div className="flex items-center justify-center h-full text-red-500">
                            <p>{error}</p>
                        </div>
                    ) : (
                        <ul className="space-y-1">
                            {filteredThreads.map(thread => {
                                // Get author from first post if available, otherwise use getUser
                                const firstPost = thread.posts[0];
                                const author = firstPost ? 
                                    getUser(thread.authorId, firstPost.authorName, firstPost.authorAvatar, firstPost.authorRole) :
                                    getUser(thread.authorId);
                                const lastPost = thread.posts[thread.posts.length - 1];
                                return (
                                <li key={thread.id} onClick={() => handleSelectThread(thread)} className={`p-3 rounded-lg cursor-pointer transition-colors ${selectedThread?.id === thread.id ? 'bg-brand-emerald-100 dark:bg-brand-emerald-900/50' : 'hover:bg-slate-200 dark:hover:bg-slate-800'}`}>
                                    <div className="flex items-center gap-2">
                                        {thread.isPinned && <Icon className="w-4 h-4 text-amber-500 flex-shrink-0" fill="currentColor"><path d="M12 17v5"/><path d="M15 17H9"/><path d="M18 8.16a3 3 0 0 0-3-3 3 3 0 0 0-3 3v5.16l2 2.82V20h2v-3.84l2-2.82V8.16Z"/></Icon>}
                                        <p className="font-semibold text-slate-800 dark:text-white truncate">{thread.title}</p>
                                    </div>
                                    <div className="flex justify-between items-center text-xs text-slate-50 dark:text-slate-400 mt-1">
                                        <span>Oleh {author.name}</span>
                                        <span>{thread.posts.length} balasan</span>
                                    </div>
                                </li>
                                )
                            })}
                        </ul>
                    )}
                </div>
            </div>

            {/* Right Column: Thread Detail / New Thread Form */}
            <div className="lg:col-span-2 bg-white dark:bg-slate-800/50 rounded-lg p-6 flex flex-col border border-slate-200 dark:border-slate-700 overflow-hidden">
                {showNewThreadForm ? (
                    <div className="flex-1 flex flex-col">
                        <h3 className="text-xl font-bold text-slate-800 dark:text-white mb-4">Buat Utas Diskusi Baru</h3>
                        <form onSubmit={handleCreateThread} className="flex-1 flex-col">
                            <input type="text" value={newThreadTitle} onChange={e => setNewThreadTitle(e.target.value)} placeholder="Judul Utas" className="w-full p-2 mb-4 rounded bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-60 text-slate-800 dark:text-white" required />
                            <textarea value={newThreadContent} onChange={e => setNewThreadContent(e.target.value)} placeholder="Tulis pertanyaan atau topik diskusi Anda di sini..." className="w-full flex-1 p-2 rounded bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white" required rows={10}></textarea>
                            <div className="mt-4 text-end">
                                <button type="button" onClick={() => setShowNewThreadForm(false)} className="px-4 py-2 rounded-lg bg-slate-200 dark:bg-slate-60 text-slate-800 dark:text-white font-semibold mr-2 hover:bg-slate-300 dark:hover:bg-slate-700">Batal</button>
                                <button 
                                type="submit" 
                                className="px-4 py-2 rounded-lg bg-brand-emerald-600 text-white font-semibold hover:bg-brand-emerald-700 disabled:opacity-50"
                                disabled={posting}
                            >
                                {posting ? 'Mengirim...' : 'Kirim'}
                            </button>
                            </div>
                        </form>
                    </div>
                ) : selectedThread ? (
                    <div className="flex-1 flex flex-col overflow-hidden">
                        <h3 className="text-xl font-bold text-slate-800 dark:text-white pb-4 border-b border-slate-200 dark:border-slate-700">{selectedThread.title}</h3>
                        <div className="flex-1 overflow-y-auto my-4">
                            <ul className="space-y-4">
                                {selectedThread.posts.map(post => {
                                    const author = getUser(post.authorId, post.authorName, post.authorAvatar, post.authorRole);
                                    const isDosen = author.role === 'Dosen' || author.role === 'Prodi Admin';
                                    return (
                                        <li key={post.id} className="flex items-start gap-4">
                                            <img src={author.avatarUrl} alt={author.name} className="w-10 h-10 rounded-full"/>
                                            <div className={`flex-1 p-3 rounded-lg ${isDosen ? 'bg-brand-sand-50 dark:bg-brand-sand-900/50 border border-brand-sand-200 dark:border-brand-sand-800' : 'bg-slate-100 dark:bg-slate-700'}`}>
                                                <div className="flex justify-between items-center">
                                                    <p className="font-semibold text-slate-700 dark:text-slate-200">{author.name} {isDosen && <span className="text-xs font-normal bg-brand-sand-200 dark:bg-brand-sand-700 px-1.5 py-0.5 rounded-sm">Dosen</span>}</p>
                                                    <p className="text-xs text-slate-500 dark:text-slate-400">{timeAgo(post.createdAt)}</p>
                                                </div>
                                                <p className="mt-2 text-slate-600 dark:text-slate-300 whitespace-pre-wrap">{post.content}</p>
                                            </div>
                                        </li>
                                    )
                                })}
                            </ul>
                        </div>
                        {!selectedThread.isClosed ? (
                             <form onSubmit={handlePostReply} className="mt-auto pt-4 border-t border-slate-200 dark:border-slate-700">
                            <textarea 
                                value={replyContent} 
                                onChange={e => setReplyContent(e.target.value)} 
                                placeholder="Tulis balasan Anda..." 
                                className="w-full p-2 mb-2 rounded bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-600" 
                                rows={3} 
                                required
                                disabled={posting}
                            ></textarea>
                            <button 
                                type="submit" 
                                className="px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg float-right disabled:opacity-50"
                                disabled={posting}
                            >
                                {posting ? 'Mengirim...' : 'Kirim Balasan'}
                            </button>
                            </form>
                        ) : (
                            <div className="text-center text-sm text-slate-50 py-4 border-t border-slate-200 dark:border-slate-70">Diskusi ini telah ditutup.</div>
                        )}
                    </div>
                ) : (
                    <div className="flex flex-col items-center justify-center h-full text-slate-50">
                        <Icon className="w-16 h-16 text-slate-300 dark:text-slate-600"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></Icon>
                        <p className="mt-2">Pilih utas untuk dibaca atau mulai utas baru.</p>
                    </div>
                )}
            </div>
        </div>
    );
};
