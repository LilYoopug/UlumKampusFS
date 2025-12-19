import React, { useState, useRef, useEffect } from 'react';
import { useLanguage } from '../contexts/LanguageContext';
import { Icon } from './Icon';

interface DropdownOption {
  value: string;
  label: string;
}

interface DropdownProps {
  options: DropdownOption[];
  value: string;
  onChange: (value: string) => void;
  placeholder?: string;
  className?: string;
}

export const Dropdown: React.FC<DropdownProps> = ({ 
  options, 
  value, 
  onChange, 
  placeholder = 'Select option',
  className = '' 
}) => {
  const { t } = useLanguage();
  const [isOpen, setIsOpen] = useState(false);
  const dropdownRef = useRef<HTMLDivElement>(null);

  const selectedOption = options.find(opt => opt.value === value) || null;

  const handleClickOutside = (event: MouseEvent) => {
    if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
      setIsOpen(false);
    }
  };

  useEffect(() => {
    document.addEventListener('mousedown', handleClickOutside);
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, []);

  return (
    <div className={`relative ${className}`} ref={dropdownRef}>
      <button
        type="button"
        className="w-full sm:w-auto px-4 py-2 rounded-full bg-slate-100 dark:bg-slate-700 border border-transparent focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white flex items-center justify-between"
        onClick={() => setIsOpen(!isOpen)}
      >
        <span>{selectedOption ? selectedOption.label : placeholder}</span>
        <Icon className="w-5 h-5 ml-2 transform transition-transform">
          <path d="m6 9 6 6 6-6" />
        </Icon>
      </button>

      {isOpen && (
        <div className="absolute z-10 mt-1 w-full bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 max-h-60 overflow-auto">
          <ul className="py-1">
            {options.map((option) => (
              <li key={option.value}>
                <button
                  type="button"
                  className={`block w-full text-left px-4 py-2 text-sm ${
                    value === option.value
                      ? 'bg-brand-emerald-100 dark:bg-brand-emerald-900/50 text-brand-emerald-700 dark:text-brand-emerald-300'
                      : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'
                  }`}
                  onClick={() => {
                    onChange(option.value);
                    setIsOpen(false);
                  }}
                >
                  {option.label}
                </button>
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
};