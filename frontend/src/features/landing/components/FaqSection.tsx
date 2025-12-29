import React, { useState } from 'react';
import { AnimatedSection } from '@/src/ui/components/AnimatedSection';
import { useLanguage } from '@/contexts/LanguageContext';
import { Icon } from '@/src/ui/components/Icon';

const AccordionItem: React.FC<{ title: string; children: React.ReactNode }> = ({ title, children }) => {
    const [isOpen, setIsOpen] = useState(false);
    return (
        <div className="border-b border-slate-200 dark:border-slate-700">
            <button
                onClick={() => setIsOpen(!isOpen)}
                className="w-full flex justify-between items-center text-start py-5 font-semibold text-slate-800 dark:text-white"
            >
                <span>{title}</span>
                <Icon className={`w-6 h-6 transition-transform transform ${isOpen ? 'rotate-180' : ''}`}>
                    <path d="m6 9 6 6 6-6"/>
                </Icon>
            </button>
            <div className={`grid transition-all duration-300 ease-in-out ${isOpen ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'}`}>
                <div className="overflow-hidden">
                    <div className="pb-5 text-slate-500 dark:text-slate-400">
                        {children}
                    </div>
                </div>
            </div>
        </div>
    );
};

export const FaqSection: React.FC = () => {
    const { t } = useLanguage();

    const faqItems = [
        {
            q: "Apakah UlumCampus terakreditasi?",
            a: "UlumCampus bekerjasama dengan lembaga pendidikan Islam terkemuka dan mengikuti standar kurikulum internasional. Kami sedang dalam proses untuk mendapatkan akreditasi penuh dari badan-badan terkait."
        },
        {
            q: "Berapa biaya pendidikannya?",
            a: "Kami menawarkan struktur biaya yang terjangkau. Biaya bervariasi tergantung program studi yang dipilih. Silakan kunjungi halaman pendaftaran untuk informasi lebih detail."
        },
        {
            q: "Apa saja persyaratan teknis untuk belajar?",
            a: "Anda hanya memerlukan perangkat (laptop/komputer/tablet), koneksi internet yang stabil, dan browser modern. Platform kami dirancang agar dapat diakses dengan mudah."
        },
        {
            q: "Bagaimana interaksi dengan dosen berlangsung?",
            a: "Interaksi dilakukan melalui sesi kelas live terjadwal, forum diskusi di setiap mata kuliah, dan sesi tanya jawab khusus. Mahasiswa juga dapat berkomunikasi melalui sistem pesan internal kami."
        }
    ];

    return (
        <AnimatedSection id="faq" className="py-20 bg-slate-50 dark:bg-brand-midnight">
          <div className="container mx-auto px-4 max-w-4xl">
            <h2 className="text-3xl md:text-4xl font-bold text-slate-800 dark:text-white text-center">{t('homepage_nav_faq')}</h2>
            <div className="mt-12">
              {faqItems.map((item, index) => (
                  <AccordionItem key={index} title={item.q}>
                    <p>{item.a}</p>
                  </AccordionItem>
              ))}
            </div>
          </div>
        </AnimatedSection>
    );
};