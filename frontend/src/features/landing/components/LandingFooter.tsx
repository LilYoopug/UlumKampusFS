import React from 'react';
import { useLanguage } from '../contexts/LanguageContext';
import { Icon } from './Icon';
import { handleNavClick } from '../App';

export const LandingFooter: React.FC = () => {
    const { t } = useLanguage();
    
    const sitemapLinks = [
        { label: t('homepage_nav_programs'), href: '#programs' },
        { label: t('homepage_nav_about'), href: '#about' },
        { label: t('homepage_nav_faq'), href: '#faq' },
        { label: t('homepage_footer_contact'), href: '#contact' },
    ];
    
    const socialLinks = [
        { label: "Facebook", href: "#", icon: <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/> },
        { label: "Twitter", href: "#", icon: <path d="M22 4s-.7 2.1-2 3.4c1.6 1.4 3.3 4.9 3 7.1a10.8 10.8 0 0 1-10 10c-3.3 1-9.5 0-12-3a8 8 0 0 1 8-1c-2.4 0-4-1-4-4 2.6.4 4.3-.9 4-3-1.6-1-3-4-1-6 2.3 2.6 5.8 4 9 4z"/> },
        { label: "Instagram", href: "#", icon: <><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></> },
        { label: "YouTube", href: "#", icon: <><path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/></> },
    ];

    return (
        <footer className="bg-slate-100 dark:bg-brand-midnight">
            <div className="container mx-auto px-4 py-12">
                <div className="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    <div className="lg:col-span-1">
                        <a href="#" onClick={handleNavClick} className="flex items-center gap-3">
                            <div className="p-2 bg-brand-emerald-500 rounded-lg">
                                <Icon className="text-white h-6 w-6"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></Icon>
                            </div>
                            <span className="text-2xl font-bold text-brand-emerald-700 dark:text-brand-emerald-400">UlumCampus</span>
                        </a>
                        <p className="mt-4 text-slate-500 dark:text-slate-400">{t('homepage_hero_subtitle')}</p>
                    </div>
                    <div className="lg:col-start-3">
                        <h3 className="font-semibold text-slate-800 dark:text-white uppercase tracking-wider">{t('homepage_footer_sitemap')}</h3>
                        <ul className="mt-4 space-y-2">
                            {sitemapLinks.map(link => (
                                <li key={link.label}>
                                    <a href={link.href} onClick={handleNavClick} className="text-slate-500 dark:text-slate-400 hover:text-brand-emerald-600 dark:hover:text-brand-emerald-400 transition-colors">{link.label}</a>
                                </li>
                            ))}
                        </ul>
                    </div>
                    <div>
                         <h3 className="font-semibold text-slate-800 dark:text-white uppercase tracking-wider">Follow Us</h3>
                         <div className="mt-4 flex gap-4">
                            {socialLinks.map(link => (
                                <a key={link.label} href={link.href} onClick={handleNavClick} title={link.label} className="text-slate-500 dark:text-slate-400 hover:text-brand-emerald-600 dark:hover:text-brand-emerald-400 transition-colors">
                                    <Icon className="w-6 h-6">{link.icon}</Icon>
                                </a>
                            ))}
                         </div>
                    </div>
                </div>
                <div className="mt-12 pt-8 border-t border-slate-200 dark:border-slate-700 text-center text-sm text-slate-500 dark:text-slate-400">
                    <p>{t('homepage_footer_copyright')}</p>
                </div>
            </div>
        </footer>
    );
};