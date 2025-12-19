import React, { useState } from 'react';
import { useLanguage } from '../contexts/LanguageContext';
import { Icon } from './Icon';
import { FaqContent } from './FaqContent';
import { UserGuideContent } from './UserGuideContent';
import { ReportBugForm } from './ReportBugForm';
import { User } from '../types';

type HelpTab = 'faq' | 'guide' | 'report';

export const Help: React.FC<{currentUser: User}> = ({ currentUser }) => {
  const { t } = useLanguage();
  const [activeTab, setActiveTab] = useState<HelpTab>('faq');

  const tabs: { id: HelpTab; labelKey: any; icon: React.ReactNode }[] = [
    { id: 'faq', labelKey: 'help_tab_faq', icon: <Icon className="w-5 h-5"><path d="M9.86 5.23A3.8 3.8 0 0 1 12 4c1.43 0 2.7.74 3.45 1.83l.02.03m-1.1 4.16a3.8 3.8 0 0 1-5.78 0l-.02-.03m5.8-4.13.02.03m-5.8 4.13-.02-.03m5.8-4.13a3.8 3.8 0 0 0-5.78 0l-.02.03"/><path d="M12 18a4 4 0 0 0 4-4H8a4 4 0 0 0 4 4z"/></Icon> },
    { id: 'guide', labelKey: 'help_tab_guide', icon: <Icon className="w-5 h-5"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></Icon> },
    { id: 'report', labelKey: 'help_tab_report_bug', icon: <Icon className="w-5 h-5"><path d="M10 21h4"/><path d="M8 3a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2Z"/><path d="m11 12-2 5h6l-2-5"/><path d="M10 17h4"/><path d="M9 8h6"/></Icon> },
  ];

  const renderContent = () => {
    switch (activeTab) {
      case 'faq':
        return <FaqContent currentUserRole={currentUser.role} />;
      case 'guide':
        return <UserGuideContent currentUserRole={currentUser.role} />;
      case 'report':
        return <ReportBugForm />;
      default:
        return null;
    }
  };

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{t('help_title')}</h1>
        <p className="text-slate-500 dark:text-slate-400 mt-1">{t('help_subtitle')}</p>
      </div>

      <div className="bg-white dark:bg-slate-800/50 rounded-2xl shadow-md p-2 sm:p-4">
        <div className="border-b border-slate-200 dark:border-slate-700">
          <nav className="-mb-px flex space-x-2 sm:space-x-6 overflow-x-auto" aria-label="Tabs">
            {tabs.map(tab => (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id)}
                className={`${
                  activeTab === tab.id
                    ? 'border-brand-emerald-500 text-brand-emerald-600 dark:text-brand-emerald-400'
                    : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:border-slate-600'
                } whitespace-nowrap flex items-center gap-2 py-3 px-2 sm:py-4 sm:px-4 border-b-2 font-medium text-sm transition-colors`}
              >
                {tab.icon}
                {t(tab.labelKey)}
              </button>
            ))}
          </nav>
        </div>

        <div className="mt-4 sm:mt-6 p-2 sm:p-4">
          {renderContent()}
        </div>
      </div>
    </div>
  );
};