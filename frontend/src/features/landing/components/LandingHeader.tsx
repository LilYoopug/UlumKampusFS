import React, { useState, useEffect } from 'react';
import { Icon } from './Icon';
import { useLanguage } from '../contexts/LanguageContext';
import { LanguageSwitcher } from './LanguageSwitcher';
import { handleNavClick } from '../App';

interface LandingHeaderProps {
  onNavigateToLogin: () => void;
  page?: 'home' | 'catalog' | 'auth';
  onBack?: () => void;
}

export const LandingHeader: React.FC<LandingHeaderProps> = ({ onNavigateToLogin, page = 'home', onBack }) => {
  const { t } = useLanguage();
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  const navLinks = [
    { label: t('homepage_nav_programs'), href: '#programs' },
    { label: t('homepage_nav_about'), href: '#about' },
    { label: t('homepage_nav_faq'), href: '#faq' },
    { label: t('homepage_footer_contact'), href: '#contact' },
  ];

  const handleBackClick = (e: React.MouseEvent<HTMLAnchorElement>) => {
    e.preventDefault();
    onBack?.();
  };

  const navClassName = `font-medium transition-colors text-slate-600 dark:text-slate-300 hover:text-brand-emerald-600 dark:hover:text-brand-emerald-400`;

  return (
    <header className={`fixed top-0 left-0 right-0 z-40 transition-all duration-300 bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg shadow-md`}>
      <div className="container mx-auto px-4">
        <div className="flex justify-between items-center h-20">
          <a href="#" onClick={onBack ? handleBackClick : handleNavClick} className="flex items-center gap-3">
             <div className="p-2 bg-brand-emerald-500 rounded-lg">
                <Icon className="text-white h-6 w-6"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></Icon>
            </div>
            <span className={`text-2xl font-bold text-brand-emerald-700 dark:text-brand-emerald-400`}>UlumCampus</span>
          </a>
          <nav className="hidden lg:flex items-center gap-8">
            {page === 'home' ? (
                navLinks.map(link => (
                    <a key={link.label} href={link.href} onClick={handleNavClick} className={navClassName}>{link.label}</a>
                ))
            ) : (
                 <a href="#" onClick={onBack} className={navClassName}>
                    Beranda
                </a>
            )}
          </nav>
          <div className="flex items-center gap-2">
            <LanguageSwitcher />
            <button onClick={onNavigateToLogin} className="hidden lg:block px-5 py-2 bg-brand-emerald-600 text-white font-semibold rounded-full hover:bg-brand-emerald-500 transition-colors">
                {t('homepage_nav_login')}
            </button>
            <button 
              onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)} 
              className={`lg:hidden p-2 rounded-md transition-colors text-slate-700 dark:text-slate-200`}
              aria-label="Toggle menu"
            >
                <Icon className="w-6 h-6">
                  {isMobileMenuOpen ? (
                    <><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></>
                  ) : (
                    <><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></>
                  )}
                </Icon>
            </button>
          </div>
        </div>
      </div>

      {/* Mobile Menu */}
      {isMobileMenuOpen && (
        <div className="lg:hidden bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg border-t border-slate-200 dark:border-slate-700">
            <nav className="flex flex-col p-4 space-y-2">
                {page === 'home' ? (
                    navLinks.map(link => (
                        <a 
                            key={link.label} 
                            href={link.href} 
                            onClick={(e) => { handleNavClick(e); setIsMobileMenuOpen(false); }} 
                            className="px-4 py-2 rounded-md text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800"
                        >
                            {link.label}
                        </a>
                    ))
                ) : (
                    <a 
                        href="#" 
                        onClick={(e) => { if(onBack) {handleBackClick(e);} setIsMobileMenuOpen(false); }} 
                        className="px-4 py-2 rounded-md text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800"
                    >
                        Beranda
                    </a>
                )}
                <button 
                    onClick={() => { onNavigateToLogin(); setIsMobileMenuOpen(false); }} 
                    className="w-full mt-4 px-5 py-2 bg-brand-emerald-600 text-white font-semibold rounded-full hover:bg-brand-emerald-500 transition-colors"
                >
                    {t('homepage_nav_login')}
                </button>
            </nav>
        </div>
      )}
    </header>
  );
};