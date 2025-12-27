import React from 'react';
import { LandingHeader } from './LandingHeader';
import { LandingFooter } from './LandingFooter';

interface LandingLayoutProps {
  children: React.ReactNode;
  onNavigateToLogin: () => void;
  page?: 'home' | 'catalog' | 'auth';
  onBack?: () => void;
}

export const LandingLayout: React.FC<LandingLayoutProps> = ({ children, onNavigateToLogin, page = 'home', onBack }) => {
  return (
    <div className="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
      <LandingHeader onNavigateToLogin={onNavigateToLogin} page={page} onBack={onBack} />
      {children}
      <LandingFooter />
    </div>
  );
};