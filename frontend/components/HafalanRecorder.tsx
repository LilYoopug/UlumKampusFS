import React, { useState, useRef, useEffect } from 'react';
import { useLanguage } from '../contexts/LanguageContext';
import { Assignment, HafalanSubmissionStatus, Submission, TajwidFeedback, User } from '../types';
import { checkTajwid } from '../services/geminiService';
import { Icon } from './Icon';

interface HafalanRecorderProps {
    assignment: Assignment;
    onNewSubmission: (submission: Submission) => void;
    currentUser: User;
}

export const HafalanRecorder: React.FC<HafalanRecorderProps> = ({ assignment, onNewSubmission, currentUser }) => {
    const { t } = useLanguage();
    const [status, setStatus] = useState<HafalanSubmissionStatus>(HafalanSubmissionStatus.NotSubmitted);
    const [audioURL, setAudioURL] = useState<string>('');
    const [audioBlob, setAudioBlob] = useState<Blob | null>(null);
    const [tajwidFeedback, setTajwidFeedback] = useState<TajwidFeedback | null>(null);
    const mediaRecorderRef = useRef<MediaRecorder | null>(null);
    const audioChunksRef = useRef<Blob[]>([]);
    const [timer, setTimer] = useState(0);
    const timerIntervalRef = useRef<ReturnType<typeof setInterval> | null>(null);
    
    const startTimer = () => {
        timerIntervalRef.current = setInterval(() => {
            setTimer(prev => prev + 1);
        }, 1000);
    };

    const stopTimer = () => {
        if (timerIntervalRef.current) {
            clearInterval(timerIntervalRef.current);
        }
        setTimer(0);
    };

    const startRecording = async () => {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorderRef.current = new MediaRecorder(stream);
            mediaRecorderRef.current.ondataavailable = (event) => {
                audioChunksRef.current.push(event.data);
            };
            mediaRecorderRef.current.onstop = () => {
                const blob = new Blob(audioChunksRef.current, { type: 'audio/wav' });
                const url = URL.createObjectURL(blob);
                setAudioBlob(blob);
                setAudioURL(url);
                audioChunksRef.current = [];
                 stream.getTracks().forEach(track => track.stop()); // Stop microphone access
            };
            mediaRecorderRef.current.start();
            setStatus(HafalanSubmissionStatus.Recording);
            startTimer();
        } catch (err) {
            console.error("Error accessing microphone:", err);
            alert(t('detail_mic_error'));
        }
    };

    const stopRecording = () => {
        if (mediaRecorderRef.current && mediaRecorderRef.current.state === 'recording') {
            mediaRecorderRef.current.stop();
            setStatus(HafalanSubmissionStatus.Submitting); // Temporary state before analysis/submission choice
            stopTimer();
        }
    };

    const handleAnalyze = async () => {
        if (!audioBlob) return;
        setStatus(HafalanSubmissionStatus.Analyzing);
        try {
            const feedback = await checkTajwid(audioBlob);
            setTajwidFeedback(feedback);
            setStatus(HafalanSubmissionStatus.FeedbackReady);
        } catch (error) {
            console.error("Tajwid check failed:", error);
            // Optionally handle the error in the UI
            setStatus(HafalanSubmissionStatus.Submitting); // Revert to previous state on error
        }
    };

    const handleSubmit = () => {
        if (!audioURL) return;
        const newSubmission: Submission = {
            studentId: currentUser.studentId,
            submittedAt: new Date().toISOString(),
            file: {
                name: `setoran_${assignment.title.replace(/\s/g, '_')}.wav`,
                url: audioURL, // Using blob URL for mock
            },
        };
        onNewSubmission(newSubmission);
        setStatus(HafalanSubmissionStatus.Submitted);
    };
    
    const handleReset = () => {
        setAudioURL('');
        setAudioBlob(null);
        setTajwidFeedback(null);
        setStatus(HafalanSubmissionStatus.NotSubmitted);
    }
    
    const formatTime = (seconds: number) => {
        const minutes = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    };

    useEffect(() => {
        return () => {
            if (timerIntervalRef.current) {
                clearInterval(timerIntervalRef.current);
            }
        };
    }, []);

    const renderFeedback = () => {
        if (!tajwidFeedback) return null;
        return (
            <div className="mt-6 w-full text-start p-4 border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                <h5 className="font-bold text-lg mb-3 text-slate-800 dark:text-white">{t('ai_feedback_title')}</h5>
                <div className="flex items-center gap-4 p-3 bg-brand-sand-100 dark:bg-brand-sand-900/50 rounded-md mb-4">
                    <div className="font-bold text-3xl text-brand-sand-700 dark:text-brand-sand-200">{tajwidFeedback.overallScore}</div>
                    <div>
                        <p className="font-semibold text-brand-sand-800 dark:text-brand-sand-100">{t('ai_feedback_score')}</p>
                        <p className="text-xs text-brand-sand-600 dark:text-brand-sand-300">{t('ai_feedback_score_desc')}</p>
                    </div>
                </div>
                <ul className="space-y-3">
                    {tajwidFeedback.feedback.map((item, index) => (
                        <li key={index} className="flex items-start gap-3">
                            <Icon className={`w-5 h-5 mt-0.5 flex-shrink-0 ${item.type === 'error' ? 'text-red-500' : 'text-blue-500'}`}>
                                {item.type === 'error' 
                                    ? <><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></>
                                    : <><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></>
                                }
                            </Icon>
                            <p className="text-sm text-slate-600 dark:text-slate-300">
                                <span className="font-semibold text-slate-700 dark:text-slate-100">{item.rule}:</span> {item.comment}
                            </p>
                        </li>
                    ))}
                </ul>
            </div>
        );
    };

    return (
        <div className="border border-slate-200 dark:border-slate-700 rounded-lg p-6 flex flex-col items-center text-center">
            <Icon className="w-12 h-12 text-brand-emerald-500 mb-2"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h9v11h-9Z"/></Icon>
            <h4 className="text-lg font-semibold text-slate-800 dark:text-white">{assignment.title}</h4>
            {assignment.description && (
              <p className="mt-1 max-w-xl text-sm text-slate-500 dark:text-slate-400">{assignment.description}</p>
            )}
            
            <div className="mt-6 w-full flex flex-col items-center">
                {status === HafalanSubmissionStatus.NotSubmitted && (
                     <button onClick={startRecording} className="flex items-center gap-2 px-6 py-3 bg-red-600 text-white font-bold rounded-full hover:bg-red-700 transition-transform transform hover:scale-105">
                        <Icon className="w-6 h-6"><circle cx="12" cy="12" r="10"/><path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0-8 0"/></Icon>
                        {t('detail_start_recording')}
                    </button>
                )}

                {status === HafalanSubmissionStatus.Recording && (
                    <>
                        <div className="text-2xl font-mono text-red-500 mb-4 animate-pulse">{formatTime(timer)}</div>
                        <button onClick={stopRecording} className="flex items-center gap-2 px-6 py-3 bg-slate-700 text-white font-bold rounded-full hover:bg-slate-800 transition-colors">
                            <Icon className="w-6 h-6" fill="currentColor"><rect x="6" y="6" width="12" height="12" rx="2"/></Icon>
                            {t('detail_stop_recording')}
                        </button>
                    </>
                )}

                {(status === HafalanSubmissionStatus.Submitting || status === HafalanSubmissionStatus.Analyzing || status === HafalanSubmissionStatus.FeedbackReady) && audioURL && (
                    <div className="w-full">
                        <audio src={audioURL} controls className="w-full mb-4" />
                        {renderFeedback()}
                        <div className="flex justify-center gap-4 mt-4">
                            <button onClick={handleReset} className="px-4 py-2 bg-slate-200 dark:bg-slate-600 text-slate-800 dark:text-white font-semibold rounded-lg hover:bg-slate-300 dark:hover:bg-slate-500 transition-colors">{t('detail_redo')}</button>
                            {status !== HafalanSubmissionStatus.FeedbackReady && (
                                <button onClick={handleAnalyze} disabled={status === HafalanSubmissionStatus.Analyzing} className="flex items-center justify-center gap-2 px-4 py-2 bg-brand-sand-600 text-white font-semibold rounded-lg hover:bg-brand-sand-700 transition-colors">
                                    <Icon className="w-5 h-5"><path d="M12.22 2h-4.44l-2 6-6 2 6 2 2 6 2-6 6-2-6-2z"/></Icon>
                                    {status === HafalanSubmissionStatus.Analyzing ? t('detail_processing') : t('detail_analyze_tajwid')}
                                </button>
                            )}
                             <button onClick={handleSubmit} className="px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors">{t('detail_submit_hafalan')}</button>
                        </div>
                    </div>
                )}
                
                {status === HafalanSubmissionStatus.Submitted && (
                    <div className="p-4 bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200 rounded-lg text-center">
                        <p className="font-semibold">{t('detail_hafalan_success')}</p>
                    </div>
                )}
            </div>
        </div>
    );
};
