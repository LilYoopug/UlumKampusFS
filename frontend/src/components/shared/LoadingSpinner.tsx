import React from 'react';
import { Icon } from '../layout/Icon';

interface LoadingSpinnerProps {
    size?: 'sm' | 'md' | 'lg';
}

export const LoadingSpinner: React.FC<LoadingSpinnerProps> = ({ size = 'md' }) => {
    const sizeClasses = {
        sm: 'w-6 h-6',
        md: 'w-8 h-8',
        lg: 'w-12 h-12'
    };

    return (
        <div className="flex flex-col justify-center items-center h-64">
            <Icon className={`${sizeClasses[size]} text-brand-emerald-500 animate-spin mb-4`}>
                <path d="M21 12a9 9 0 1 1-6.219-8.56" />
            </Icon>
            <p className="text-slate-600 dark:text-slate-400 text-sm">Memuat...</p>
        </div>
    );
};