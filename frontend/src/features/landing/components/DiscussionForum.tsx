import React, { useState, useMemo, useEffect } from 'react';
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

type MobileView = 'threads' | 'chat';

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
    const [mobileView, setMobileView] = useState<MobileView>('threads');

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
        setMobileView('chat'); // Switch to chat view on mobile
    };

    const handleBackToThreads = () => {
        setMobileView('threads');
        setSelectedThread(null);
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
        // Use the provided data from API response
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
        <div className="flex flex-col lg:grid lg:grid-cols-3 gap-4 lg:gap-6 h-[calc(100vh-12rem)] lg:h-[calc(100vh-14rem)] min-h-[500px] overflow-hidden">
            {/* Left Column: Thread List - Hidden on mobile when viewing chat */}
            <div className={`${mobileView === 'chat' ? 'hidden' : 'flex'} lg:flex lg:col-span-1 bg-slate-50 dark:bg-slate-900/50 rounded-xl p-4 lg:p-4 flex-col border border-slate-200 dark:border-slate-700 h-full`}>
                <div className="relative mb-3">
                    <Icon className="absolute start-3 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></Icon>
                    <input
                        type="text"
                        placeholder="Cari utas..."
                        value={searchTerm}
                        onChange={e => setSearchTerm(e.target.value)}
                        className="w-full ps-10 pe-4 py-3 text-base rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-emerald-500 focus:border-transparent transition-all"
                    />
                </div>
                <button
                    onClick={() => { setShowNewThreadForm(true); setSelectedThread(null); setMobileView('chat'); }}
                    className="w-full mb-3 px-4 py-3 bg-brand-emerald-600 text-white font-semibold rounded-xl hover:bg-brand-emerald-700 active:scale-[0.98] transition-all text-base"
                >
                    + Mulai Utas Baru
                </button>
                <div className="flex-1 overflow-y-auto -mx-2 px-2">
                    {loading ? (
                        <div className="flex items-center justify-center h-full text-slate-500">
                            <div className="flex flex-col items-center gap-3">
                                <div className="w-8 h-8 border-2 border-brand-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                                <p className="text-base">Memuat diskusi...</p>
                            </div>
                        </div>
                    ) : error ? (
                        <div className="flex items-center justify-center h-full text-red-500">
                            <p className="text-base">{error}</p>
                        </div>
                    ) : filteredThreads.length === 0 ? (
                        <div className="flex flex-col items-center justify-center h-full text-slate-400 py-8">
                            <Icon className="w-12 h-12 mb-3"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></Icon>
                            <p className="text-base text-center">Belum ada diskusi.<br/>Mulai utas baru!</p>
                        </div>
                    ) : (
                        <ul className="space-y-2">
                            {filteredThreads.map(thread => {
                                const firstPost = thread.posts[0];
                                const author = firstPost ?
                                    getUser(thread.authorId, firstPost.authorName, firstPost.authorAvatar, firstPost.authorRole) :
                                    getUser(thread.authorId);
                                return (
                                <li
                                    key={thread.id}
                                    onClick={() => handleSelectThread(thread)}
                                    className={`p-4 rounded-xl cursor-pointer transition-all active:scale-[0.98] ${selectedThread?.id === thread.id ? 'bg-brand-emerald-100 dark:bg-brand-emerald-900/50 border-2 border-brand-emerald-500' : 'bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700'}`}
                                >
                                    <div className="flex items-start gap-3">
                                        {thread.isPinned && <Icon className="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor"><path d="M12 17v5"/><path d="M15 17H9"/><path d="M18 8.16a3 3 0 0 0-3-3 3 3 0 0 0-3 3v5.16l2 2.82V20h2v-3.84l2-2.82V8.16Z"/></Icon>}
                                        <div className="flex-1 min-w-0">
                                            <p className="font-semibold text-slate-800 dark:text-white text-base leading-snug line-clamp-2">{thread.title}</p>
                                            <div className="flex items-center gap-2 mt-2 text-sm text-slate-500 dark:text-slate-400">
                                                <span className="truncate">{author.name}</span>
                                                <span>•</span>
                                                <span className="flex-shrink-0">{thread.posts.length} balasan</span>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                )
                            })}
                        </ul>
                    )}
                </div>
            </div>

            {/* Right Column: Thread Detail / New Thread Form - Fullscreen on mobile */}
            <div className={`${mobileView === 'threads' ? 'hidden' : 'flex'} lg:flex lg:col-span-2 bg-white dark:bg-slate-800/50 rounded-xl flex-col border border-slate-200 dark:border-slate-700 overflow-hidden h-full`}>
                {showNewThreadForm ? (
                    <div className="flex-1 flex flex-col p-4 lg:p-6">
                        {/* Mobile back button */}
                        <button
                            onClick={handleBackToThreads}
                            className="lg:hidden flex items-center gap-2 text-slate-600 dark:text-slate-300 mb-4 -ml-1 py-2"
                        >
                            <Icon className="w-5 h-5"><path d="m15 18-6-6 6-6"/></Icon>
                            <span className="text-base font-medium">Kembali</span>
                        </button>
                        <h3 className="text-xl lg:text-2xl font-bold text-slate-800 dark:text-white mb-4">Buat Utas Diskusi Baru</h3>
                        <form onSubmit={handleCreateThread} className="flex-1 flex flex-col">
                            <input
                                type="text"
                                value={newThreadTitle}
                                onChange={e => setNewThreadTitle(e.target.value)}
                                placeholder="Judul Utas"
                                className="w-full p-3 lg:p-4 mb-4 rounded-xl bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white text-base lg:text-lg focus:ring-2 focus:ring-brand-emerald-500 focus:border-transparent"
                                required
                            />
                            <textarea
                                value={newThreadContent}
                                onChange={e => setNewThreadContent(e.target.value)}
                                placeholder="Tulis pertanyaan atau topik diskusi Anda di sini..."
                                className="w-full flex-1 p-3 lg:p-4 rounded-xl bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white text-base lg:text-lg focus:ring-2 focus:ring-brand-emerald-500 focus:border-transparent resize-none"
                                required
                                rows={8}
                            ></textarea>
                            <div className="mt-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                                <button
                                    type="button"
                                    onClick={handleBackToThreads}
                                    className="w-full sm:w-auto px-6 py-3 rounded-xl bg-slate-200 dark:bg-slate-600 text-slate-800 dark:text-white font-semibold hover:bg-slate-300 dark:hover:bg-slate-500 transition-all text-base"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    className="w-full sm:w-auto px-6 py-3 rounded-xl bg-brand-emerald-600 text-white font-semibold hover:bg-brand-emerald-700 disabled:opacity-50 transition-all text-base"
                                    disabled={posting}
                                >
                                    {posting ? 'Mengirim...' : 'Kirim'}
                                </button>
                            </div>
                        </form>
                    </div>
                ) : selectedThread ? (
                    <div className="flex-1 flex flex-col overflow-hidden">
                        {/* Header with back button */}
                        <div className="p-4 lg:p-6 border-b border-slate-200 dark:border-slate-700">
                            <button
                                onClick={handleBackToThreads}
                                className="lg:hidden flex items-center gap-2 text-slate-600 dark:text-slate-300 mb-3 -ml-1"
                            >
                                <Icon className="w-5 h-5"><path d="m15 18-6-6 6-6"/></Icon>
                                <span className="text-base font-medium">Kembali</span>
                            </button>
                            <h3 className="text-lg lg:text-xl font-bold text-slate-800 dark:text-white leading-snug">{selectedThread.title}</h3>
                            <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">{selectedThread.posts.length} balasan</p>
                        </div>

                        {/* Posts/Chat area */}
                        <div className="flex-1 overflow-y-auto p-4 lg:p-6">
                            <ul className="space-y-4 lg:space-y-5">
                                {selectedThread.posts.map(post => {
                                    const author = getUser(post.authorId, post.authorName, post.authorAvatar, post.authorRole);
                                    const isDosen = author.role === 'Dosen' || author.role === 'Prodi Admin';
                                    return (
                                        <li key={post.id} className="flex items-start gap-3 lg:gap-4">
                                            <img src={author.avatarUrl} alt={author.name} className="w-10 h-10 lg:w-12 lg:h-12 rounded-full flex-shrink-0 object-cover"/>
                                            <div className={`flex-1 p-3 lg:p-4 rounded-2xl ${isDosen ? 'bg-brand-sand-50 dark:bg-brand-sand-900/50 border border-brand-sand-200 dark:border-brand-sand-800' : 'bg-slate-100 dark:bg-slate-700'}`}>
                                                <div className="flex flex-wrap items-center gap-2 mb-2">
                                                    <p className="font-semibold text-slate-800 dark:text-slate-200 text-base">{author.name}</p>
                                                    {isDosen && <span className="text-xs font-medium bg-brand-sand-200 dark:bg-brand-sand-700 text-brand-sand-800 dark:text-brand-sand-200 px-2 py-0.5 rounded-full">Dosen</span>}
                                                    <span className="text-xs text-slate-500 dark:text-slate-400 ml-auto">{timeAgo(post.createdAt)}</span>
                                                </div>
                                                <p className="text-base lg:text-[15px] text-slate-700 dark:text-slate-300 whitespace-pre-wrap leading-relaxed">{post.content}</p>
                                            </div>
                                        </li>
                                    )
                                })}
                            </ul>
                        </div>

                        {/* Reply form */}
                        {!selectedThread.isClosed ? (
                            <form onSubmit={handlePostReply} className="p-4 lg:p-6 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/30">
                                <div className="flex gap-3">
                                    <textarea
                                        value={replyContent}
                                        onChange={e => setReplyContent(e.target.value)}
                                        placeholder="Tulis balasan Anda..."
                                        className="flex-1 p-3 lg:p-4 rounded-xl bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-white text-base focus:ring-2 focus:ring-brand-emerald-500 focus:border-transparent resize-none"
                                        rows={2}
                                        required
                                        disabled={posting}
                                    ></textarea>
                                    <button
                                        type="submit"
                                        className="self-end px-4 lg:px-6 py-3 bg-brand-emerald-600 text-white font-semibold rounded-xl disabled:opacity-50 hover:bg-brand-emerald-700 transition-all flex-shrink-0"
                                        disabled={posting}
                                    >
                                        <span className="hidden sm:inline">{posting ? 'Mengirim...' : 'Kirim'}</span>
                                        <Icon className="w-5 h-5 sm:hidden"><path d="m22 2-7 20-4-9-9-4 20-7z"/><path d="M22 2 11 13"/></Icon>
                                    </button>
                                </div>
                            </form>
                        ) : (
                            <div className="p-4 lg:p-6 text-center text-sm text-slate-500 dark:text-slate-400 border-t border-slate-200 dark:border-slate-700">Diskusi ini telah ditutup.</div>
                        )}
                    </div>
                ) : (
                    <div className="flex flex-col items-center justify-center h-full text-slate-400 p-6">
                        <Icon className="w-16 h-16 lg:w-20 lg:h-20 text-slate-300 dark:text-slate-600 mb-4"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></Icon>
                        <p className="text-base text-center">Pilih utas untuk dibaca<br/>atau mulai utas baru.</p>
                    </div>
                )}
            </div>
        </div>
    );
};
