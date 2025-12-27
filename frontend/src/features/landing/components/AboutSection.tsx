import React from 'react';
import { AnimatedSection } from './AnimatedSection';
import { useLanguage } from '../contexts/LanguageContext';

export const AboutSection: React.FC = () => {
    const { t } = useLanguage();
    return (
        <AnimatedSection id="about" className="py-20 bg-white dark:bg-slate-900">
          <div className="container mx-auto px-4">
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
              <div>
                <h2 className="text-3xl md:text-4xl font-bold text-slate-800 dark:text-white">{t('homepage_nav_about')}</h2>
                <p className="mt-4 text-slate-500 dark:text-slate-400 leading-relaxed">
                  UlumCampus adalah platform pendidikan tinggi Islam yang didedikasikan untuk menyebarkan ilmu syar'i yang otentik dengan metodologi modern. Visi kami adalah menjadi rujukan utama pendidikan Islam online global yang melahirkan generasi berilmu, beriman, dan berakhlak mulia.
                </p>
                <p className="mt-4 text-slate-500 dark:text-slate-400 leading-relaxed">
                  Misi kami adalah menyediakan akses pendidikan berkualitas tinggi yang fleksibel, didukung oleh para pakar di bidangnya dan teknologi pembelajaran terdepan, untuk seluruh kaum muslimin di mana pun mereka berada.
                </p>
              </div>
              <div>
                <img src="https://picsum.photos/seed/about/600/400" alt="Tentang UlumCampus" className="rounded-2xl shadow-lg"/>
              </div>
            </div>
          </div>
        </AnimatedSection>
    );
};
