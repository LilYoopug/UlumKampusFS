import React from 'react';
import { useLanguage } from '@/contexts/LanguageContext';
import { Icon } from '@/src/ui/components/Icon';
import { PrayerTimes } from '@/src/features/resources/components/PrayerTimes';
import { IslamicResources } from '@/src/features/resources/components/IslamicResources';

export const Worship: React.FC = () => {
  const { t } = useLanguage();
  return (
    <div className="space-y-8">
      <div className="flex items-center gap-4">
         <Icon className="w-10 h-10 text-brand-emerald-500">
            <path d="M12.22 2h-4.44l-2 6-6 2 6 2 2 6 2-6 6-2-6-2z"/><path d="M20.91 14.65a2.43 2.43 0 0 0-2.26 2.26l.09.63a2.43 2.43 0 0 0 2.26 2.26l.63.09a2.43 2.43 0 0 0 2.26-2.26l-.09-.63a2.43 2.43 0 0 0-2.26-2.26Z"/><path d="M17 21.5a.5.5 0 1 0-1 0 .5.5 0 0 0 1 0Z"/>
         </Icon>
         <div>
            <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{t('sidebar_worship')}</h1>
            <p className="text-slate-500 dark:text-slate-400 mt-1">Lacak dan tingkatkan amalan ibadah harian Anda.</p>
         </div>
      </div>

      <div>
        <h2 className="text-2xl font-bold mb-4 text-slate-800 dark:text-white">Jadwal Shalat Hari Ini</h2>
        <PrayerTimes />
      </div>
      
      <IslamicResources />

    </div>
  );
};
