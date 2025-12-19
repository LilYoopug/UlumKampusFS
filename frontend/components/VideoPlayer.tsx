import React, { useState, useRef, useEffect, useCallback } from 'react';
import { Icon } from './Icon';

interface VideoPlayerProps {
    videoUrl: string;
    captionsUrl?: string;
    title: string;
    courseTitle: string;
    onClose: () => void;
    onGoToCourse: () => void;
}

export const VideoPlayer: React.FC<VideoPlayerProps> = ({ videoUrl, captionsUrl, title, courseTitle, onClose, onGoToCourse }) => {
    const videoRef = useRef<HTMLVideoElement>(null);
    const containerRef = useRef<HTMLDivElement>(null);
    const progressRef = useRef<HTMLDivElement>(null);

    const [isPlaying, setIsPlaying] = useState(false);
    const [volume, setVolume] = useState(1);
    const [isMuted, setIsMuted] = useState(false);
    const [duration, setDuration] = useState(0);
    const [currentTime, setCurrentTime] = useState(0);
    const [playbackRate, setPlaybackRate] = useState(1);
    const [areCaptionsVisible, setAreCaptionsVisible] = useState(false);
    const [showControls, setShowControls] = useState(true);
    const controlsTimeout = useRef<number | null>(null);

    const formatTime = (time: number) => {
        if (isNaN(time)) return '0:00';
        const minutes = Math.floor(time / 60);
        const seconds = Math.floor(time % 60);
        return `${minutes}:${seconds.toString().padStart(2, '0')}`;
    };

    const handlePlayPause = () => {
        if (videoRef.current) {
            if (isPlaying) {
                videoRef.current.pause();
            } else {
                videoRef.current.play();
            }
            setIsPlaying(!isPlaying);
        }
    };
    
    const handleVolumeChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const newVolume = parseFloat(e.target.value);
        setVolume(newVolume);
        if (videoRef.current) {
            videoRef.current.volume = newVolume;
            videoRef.current.muted = newVolume === 0;
        }
        setIsMuted(newVolume === 0);
    };

    const toggleMute = () => {
        if (videoRef.current) {
            const currentlyMuted = videoRef.current.muted;
            videoRef.current.muted = !currentlyMuted;
            setIsMuted(!currentlyMuted);
            if (currentlyMuted) { // if it was muted, and we're unmuting
                setVolume(videoRef.current.volume > 0 ? videoRef.current.volume : 0.5);
            }
        }
    };

    const handleProgressSeek = (e: React.MouseEvent<HTMLDivElement>) => {
        if (progressRef.current && videoRef.current) {
            const rect = progressRef.current.getBoundingClientRect();
            const pos = (e.clientX - rect.left) / rect.width;
            videoRef.current.currentTime = pos * duration;
        }
    };

    const handlePlaybackRateChange = (rate: number) => {
        setPlaybackRate(rate);
        if (videoRef.current) {
            videoRef.current.playbackRate = rate;
        }
    };

    const toggleCaptions = () => {
        if (videoRef.current && videoRef.current.textTracks.length > 0) {
            const track = videoRef.current.textTracks[0];
            const isShowing = track.mode === 'showing';
            track.mode = isShowing ? 'hidden' : 'showing';
            setAreCaptionsVisible(!isShowing);
        }
    };
    
    const toggleFullscreen = () => {
        if (containerRef.current) {
            if (!document.fullscreenElement) {
                containerRef.current.requestFullscreen().catch(err => {
                    alert(`Error attempting to enable full-screen mode: ${err.message} (${err.name})`);
                });
            } else {
                document.exitFullscreen();
            }
        }
    };

    const handleMouseMove = () => {
        setShowControls(true);
        if (controlsTimeout.current) {
            clearTimeout(controlsTimeout.current);
        }
        controlsTimeout.current = window.setTimeout(() => {
            if (isPlaying) setShowControls(false);
        }, 3000);
    };
    
    useEffect(() => {
        const video = videoRef.current;
        if (!video) return;

        const onLoadedMetadata = () => setDuration(video.duration);
        const onTimeUpdate = () => setCurrentTime(video.currentTime);
        const onPlay = () => setIsPlaying(true);
        const onPause = () => setIsPlaying(false);
        const onVolumeChange = () => {
             setVolume(video.volume);
             setIsMuted(video.muted);
        }

        video.addEventListener('loadedmetadata', onLoadedMetadata);
        video.addEventListener('timeupdate', onTimeUpdate);
        video.addEventListener('play', onPlay);
        video.addEventListener('pause', onPause);
        video.addEventListener('volumechange', onVolumeChange);
        
        video.play().catch(console.error);
        
        if (video.textTracks.length > 0) {
            const track = video.textTracks[0];
            track.mode = areCaptionsVisible ? 'showing' : 'hidden';
        }

        return () => {
            video.removeEventListener('loadedmetadata', onLoadedMetadata);
            video.removeEventListener('timeupdate', onTimeUpdate);
            video.removeEventListener('play', onPlay);
            video.removeEventListener('pause', onPause);
            video.removeEventListener('volumechange', onVolumeChange);
            if (controlsTimeout.current) {
                clearTimeout(controlsTimeout.current);
            }
        }
    }, [areCaptionsVisible]);

    const handleKeyDown = useCallback((event: KeyboardEvent) => {
        if (event.key === 'Escape' && !document.fullscreenElement) {
            onClose();
        }
    }, [onClose]);

    useEffect(() => {
        document.addEventListener('keydown', handleKeyDown);
        return () => {
            document.removeEventListener('keydown', handleKeyDown);
        };
    }, [handleKeyDown]);
    
    return (
        <div className="fixed inset-0 bg-black/80 flex items-center justify-center z-50" onClick={onClose} role="dialog" aria-modal="true" aria-labelledby="video-title">
            <div ref={containerRef} className="relative w-full max-w-4xl bg-black aspect-video flex flex-col overflow-hidden shadow-2xl rounded-lg" onClick={e => e.stopPropagation()} onMouseMove={handleMouseMove} onMouseLeave={() => isPlaying && setShowControls(false)}>
                
                <video ref={videoRef} className="w-full h-full" onClick={handlePlayPause}>
                    <source src={videoUrl} type="video/mp4" />
                    {captionsUrl && <track src={captionsUrl} kind="subtitles" srcLang="en" label="English" />}
                    Your browser does not support the video tag.
                </video>

                <div className={`absolute inset-0 transition-opacity duration-300 ${showControls ? 'opacity-100' : 'opacity-0'}`}>
                    {/* Header */}
                    <div className="absolute top-0 left-0 right-0 p-4 bg-gradient-to-b from-black/70 to-transparent">
                        <div className="flex justify-between items-start">
                            <div className="flex-1 overflow-hidden">
                                <h3 id="video-title" className="text-white font-bold text-lg truncate">{title}</h3>
                                <button onClick={onGoToCourse} className="text-slate-300 text-sm hover:underline cursor-pointer truncate text-left">
                                    {courseTitle}
                                </button>
                            </div>
                            <button onClick={onClose} className="text-white/80 hover:text-white flex-shrink-0 ml-4" aria-label="Close player">
                                <Icon className="w-8 h-8"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></Icon>
                            </button>
                        </div>
                    </div>

                    {/* Controls */}
                    <div className="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black/70 to-transparent text-white">
                        {/* Progress Bar */}
                        <div ref={progressRef} onClick={handleProgressSeek} className="w-full h-2.5 bg-white/20 rounded-full cursor-pointer group mb-2 flex items-center">
                           <div className="h-full bg-brand-emerald-500 rounded-full relative" style={{ width: `${(currentTime / duration) * 100}%` }}>
                                <div className="absolute right-0 top-1/2 -translate-y-1/2 w-4 h-4 bg-white rounded-full opacity-0 group-hover:opacity-100 transform scale-0 group-hover:scale-100 transition-all"></div>
                           </div>
                        </div>

                        {/* Buttons & Sliders */}
                        <div className="flex justify-between items-center">
                            <div className="flex items-center gap-4">
                                <button onClick={handlePlayPause} aria-label={isPlaying ? 'Pause' : 'Play'}>
                                    {isPlaying ? <Icon className="w-6 h-6"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></Icon> : <Icon className="w-6 h-6"><polygon points="5 3 19 12 5 21 5 3"/></Icon>}
                                </button>
                                <div className="flex items-center gap-2">
                                    <button onClick={toggleMute} aria-label={isMuted ? 'Unmute' : 'Mute'}>
                                        {isMuted || volume === 0 ? <Icon className="w-6 h-6"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><line x1="22" y1="9" x2="16" y2="15"/><line x1="16" y1="9" x2="22" y2="15"/></Icon> : <Icon className="w-6 h-6"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/></Icon>}
                                    </button>
                                    <input type="range" min="0" max="1" step="0.05" value={isMuted ? 0 : volume} onChange={handleVolumeChange} className="w-24 accent-brand-emerald-500" aria-label="Volume control" />
                                </div>
                                <div className="text-sm font-mono" aria-label="Video time">
                                    {formatTime(currentTime)} / {formatTime(duration)}
                                </div>
                            </div>
                            <div className="flex items-center gap-4">
                                 <div className="relative group">
                                     <button className="text-sm font-semibold w-12">{playbackRate}x</button>
                                     <div className="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 bg-slate-900/80 rounded-md p-1 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none group-hover:pointer-events-auto">
                                         {[0.5, 0.75, 1, 1.25, 1.5, 2].map(rate => (
                                             <button key={rate} onClick={() => handlePlaybackRateChange(rate)} className={`block w-full text-left px-3 py-1 text-sm rounded ${playbackRate === rate ? 'bg-brand-emerald-600' : ''} hover:bg-brand-emerald-500`}>
                                                 {rate}x
                                             </button>
                                         ))}
                                     </div>
                                 </div>
                                {captionsUrl && (
                                    <button onClick={toggleCaptions} title="Captions" aria-label="Toggle captions">
                                        <Icon className={`w-6 h-6 ${areCaptionsVisible ? 'text-brand-emerald-400' : ''}`}><path d="M17 11h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3H13"/><path d="M21 15a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1Z"/><path d="M9.5 11H7a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3H5"/></Icon>
                                    </button>
                                )}
                                <button onClick={toggleFullscreen} title="Fullscreen" aria-label="Toggle fullscreen">
                                    <Icon className="w-6 h-6"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></Icon>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};