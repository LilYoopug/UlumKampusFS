import React, { useState, useMemo, useEffect } from 'react';
import { DISCUSSION_THREADS, ALL_USERS } from '../constants';
import { DiscussionThread, DiscussionPost, User } from '../types';
import { useLanguage } from '../contexts/LanguageContext';
import { Icon } from './Icon';
import { timeAgo } from '../utils/time';

interface DiscussionForumProps {
    courseId: string;
    currentUser: User;
    initialThreadId?: string;
}

export const DiscussionForum: React.FC<DiscussionForumProps> = ({ courseId, currentUser, initialThreadId }) => {
    const { t } = useLanguage();
    const [threads, setThreads] = useState<DiscussionThread[]>(() => DISCUSSION_THREADS.filter(t => t.courseId === courseId));
    const [selectedThread, setSelectedThread] = useState<DiscussionThread | null>(null);
    const [searchTerm, setSearchTerm] = useState('');
    const [showNewThreadForm, setShowNewThreadForm] = useState(false);
    const [newThreadTitle, setNewThreadTitle] = useState('');
    const [newThreadContent, setNewThreadContent] = useState('');
    const [replyContent, setReplyContent] = useState('');

    useEffect(() => {
      if (initialThreadId) {
          const threadToSelect = threads.find(t => t.id === initialThreadId);
          if (threadToSelect) {
              setSelectedThread(threadToSelect);
          }
      }
    }, [initialThreadId, threads]);

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

    const handleCreateThread = (e: React.FormEvent) => {
        e.preventDefault();
        if (!newThreadTitle.trim() || !newThreadContent.trim()) return;
        
        const newThread: DiscussionThread = {
            id: `DT${Date.now()}`,
            courseId: courseId,
            title: newThreadTitle,
            authorId: currentUser.studentId,
            createdAt: new Date().toISOString(),
            isPinned: false,
            isClosed: false,
            posts: [{
                id: `P${Date.now()}`,
                authorId: currentUser.studentId,
                createdAt: new Date().toISOString(),
                content: newThreadContent,
            }]
        };

        setThreads(prev => [newThread, ...prev]);
        setSelectedThread(newThread);
        setShowNewThreadForm(false);
        setNewThreadTitle('');
        setNewThreadContent('');
    };

    const handlePostReply = (e: React.FormEvent) => {
        e.preventDefault();
        if (!replyContent.trim() || !selectedThread || selectedThread.isClosed) return;

        const newPost: DiscussionPost = {
            id: `P${Date.now()}`,
            authorId: currentUser.studentId,
            createdAt: new Date().toISOString(),
            content: replyContent,
        };

        const updatedThreads = threads.map(thread => {
            if (thread.id === selectedThread.id) {
                return { ...thread, posts: [...thread.posts, newPost] };
            }
            return thread;
        });

        setThreads(updatedThreads);
        setSelectedThread(prev => prev ? { ...prev, posts: [...prev.posts, newPost] } : null);
        setReplyContent('');
    };

    const getUser = (authorId: string): User => {
        const user = ALL_USERS.find(u => u.studentId === authorId);
        return user || { 
            name: 'Pengguna tidak dikenal', 
            avatarUrl: '',
            role: 'Mahasiswa', // Default role - using correct UserRole type
            studentId: authorId,
            email: '',
            joinDate: '',
            bio: ''
        };
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
                    <ul className="space-y-1">
                        {filteredThreads.map(thread => {
                            const author = getUser(thread.authorId);
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
                                <button type="submit" className="px-4 py-2 rounded-lg bg-brand-emerald-600 text-white font-semibold hover:bg-brand-emerald-700">Kirim</button>
                            </div>
                        </form>
                    </div>
                ) : selectedThread ? (
                    <div className="flex-1 flex flex-col overflow-hidden">
                        <h3 className="text-xl font-bold text-slate-800 dark:text-white pb-4 border-b border-slate-200 dark:border-slate-700">{selectedThread.title}</h3>
                        <div className="flex-1 overflow-y-auto my-4">
                            <ul className="space-y-4">
                                {selectedThread.posts.map(post => {
                                    const author = getUser(post.authorId);
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
                                <textarea value={replyContent} onChange={e => setReplyContent(e.target.value)} placeholder="Tulis balasan Anda..." className="w-full p-2 mb-2 rounded bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-600" rows={3} required></textarea>
                                <button type="submit" className="px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg float-right">Kirim Balasan</button>
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
