import React from 'react';
import { AnimatedSection } from './AnimatedSection';
import { useLanguage } from '../contexts/LanguageContext';
import { Icon } from './Icon';

export const ContactSection: React.FC = () => {
    const { t } = useLanguage();
    return (
        <AnimatedSection id="contact" className="py-20 bg-white dark:bg-slate-900">
          <div className="container mx-auto px-4 text-center">
            <h2 className="text-3xl md:text-4xl font-bold text-slate-800 dark:text-white">{t('homepage_footer_contact')}</h2>
            <p className="mt-2 text-slate-500 dark:text-slate-400 max-w-2xl mx-auto">
                Kami siap membantu Anda. Hubungi kami melalui kanal di bawah ini untuk pertanyaan seputar akademik, pendaftaran, atau dukungan teknis.
            </p>
            <div className="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <div className="p-8 bg-white dark:bg-slate-800/50 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-700">
                    <Icon className="w-12 h-12 mx-auto text-brand-emerald-500"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></Icon>
                    <h3 className="mt-4 text-xl font-bold">Email</h3>
                    <p className="mt-2 text-slate-500 dark:text-slate-400">Hubungi tim admisi dan dukungan kami.</p>
                    <a href="mailto:info@ulumcampus.com" className="mt-4 inline-block font-semibold text-brand-emerald-600 hover:underline">info@ulumcampus.com</a>
                </div>
                <div className="p-8 bg-white dark:bg-slate-800/50 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-700">
                    <Icon className="w-12 h-12 mx-auto text-brand-emerald-500"><path d="M14.05 2a9.95 9.95 0 0 0-9.9 9.95c0 6.05 5.8 8.45 8.55 10.85a2.1 2.1 0 0 0 2.7 0c2.75-2.4 8.55-4.8 8.55-10.85a9.95 9.95 0 0 0-9.9-9.95Z"/><circle cx="12" cy="12" r="3"/></Icon>
                    <h3 className="mt-4 text-xl font-bold">Alamat Kampus</h3>
                    <p className="mt-2 text-slate-500 dark:text-slate-400">Gedung Ulum Digital, Jl. Ilmu Syar'i No. 1, Jakarta, Indonesia</p>
                </div>
                <div className="p-8 bg-white dark:bg-slate-800/50 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-700">
                    <Icon className="w-12 h-12 mx-auto text-brand-emerald-500"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></Icon>
                    <h3 className="mt-4 text-xl font-bold">Telepon</h3>
                    <p className="mt-2 text-slate-500 dark:text-slate-400">Layanan mahasiswa pada jam kerja.</p>
                    <a href="tel:+622112345678" className="mt-4 inline-block font-semibold text-brand-emerald-600 hover:underline">+62 21 1234 5678</a>
                </div>
            </div>
          </div>
        </AnimatedSection>
    );
};
