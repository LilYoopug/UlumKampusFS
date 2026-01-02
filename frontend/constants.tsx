import React from 'react';
import { Badge } from '@/types';
import { Icon } from '@/src/ui/components/Icon';

// Badge definitions - UI-related constants that don't need to come from backend
export const BADGES: Badge[] = [
  {
    id: 'learner',
    icon: <Icon className="w-8 h-8"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></Icon>,
    titleKey: 'badge_learner_title',
    descriptionKey: 'badge_learner_desc',
  },
  {
    id: 'fiqh',
    icon: <Icon className="w-8 h-8"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></Icon>,
    titleKey: 'badge_fiqh_title',
    descriptionKey: 'badge_fiqh_desc',
  },
  {
    id: 'historian',
    icon: <Icon className="w-8 h-8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></Icon>,
    titleKey: 'badge_historian_title',
    descriptionKey: 'badge_historian_desc',
  },
  {
    id: 'aqidah_foundations',
    icon: <Icon className="w-8 h-8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M12 2a9 9 0 0 0-9 9c0 4.28 2.5 8 9 12 6.5-4 9-7.72 9-12a9 9 0 0 0-9-9z"/></Icon>,
    titleKey: 'badge_aqidah_title',
    descriptionKey: 'badge_aqidah_desc',
  },
  {
    id: 'muamalat_expert',
    icon: <Icon className="w-8 h-8"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></Icon>,
    titleKey: 'badge_muamalat_title',
    descriptionKey: 'badge_muamalat_desc',
  },
];

// Payment method icons - UI-related constants
export const PAYMENT_METHOD_ICONS: Record<string, React.ReactNode> = {
  bank_transfer: (
    <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <path strokeLinecap="round" strokeLinejoin="round" d="M3 21l18 0M12 3v18m-9-9l9-9 9 9" />
    </svg>
  ),
  credit_card: (
    <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <path strokeLinecap="round" strokeLinejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
    </svg>
  ),
  e_wallet: (
    <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <path strokeLinecap="round" strokeLinejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
    </svg>
  ),
  virtual_account: (
    <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <path strokeLinecap="round" strokeLinejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
    </svg>
  ),
};

// Payment types for administration page
export interface PaymentItem {
  id: string;
  item_id: string;
  title_key: string;
  description_key: string;
  amount: number;
  status: 'paid' | 'unpaid' | 'pending';
  due_date?: string;
}

export interface PaymentHistoryItem {
  id: string;
  payment_item_id: string;
  amount: number;
  payment_method: string;
  transaction_id: string;
  paid_at: string;
  status: 'success' | 'pending' | 'failed';
}

export interface PaymentMethod {
  id: string;
  name: string;
  type: string;
  icon?: string;
  is_active: boolean;
}

// Default payment methods (will be fetched from API in production)
export const PAYMENT_METHODS: PaymentMethod[] = [
  { id: '1', name: 'Bank Transfer BCA', type: 'bank_transfer', is_active: true },
  { id: '2', name: 'Bank Transfer Mandiri', type: 'bank_transfer', is_active: true },
  { id: '3', name: 'GoPay', type: 'e_wallet', is_active: true },
  { id: '4', name: 'OVO', type: 'e_wallet', is_active: true },
  { id: '5', name: 'Credit Card', type: 'credit_card', is_active: true },
];
