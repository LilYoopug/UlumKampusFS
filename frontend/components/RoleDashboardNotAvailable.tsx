import React from 'react';

export const RolePageNotAvailable: React.FC = () => {
  return (
    <div className="p-6 flex items-center justify-center min-h-[70vh]">
      <div className="text-center">
        <div className="mx-auto w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
          <svg className="w-8 h-8 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.364 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
        </div>
        <h1 className="text-2xl font-bold text-slate-800 dark:text-white mb-2">Page not available for this role</h1>
        <p className="text-slate-600 dark:text-slate-400">The page you are trying to access is not available with your current role.</p>
      </div>
    </div>
  );
};