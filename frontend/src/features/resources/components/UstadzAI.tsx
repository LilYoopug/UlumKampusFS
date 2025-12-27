import React, { useState, useRef, useEffect } from 'react';
import { Icon } from './Icon';
import { askUstadzAI } from '../services/geminiService';
import { useLanguage } from '../contexts/LanguageContext';

interface Message {
  sender: 'user' | 'ai';
  text: string;
}

export const UstadzAI: React.FC = () => {
  const { t } = useLanguage();
  const [isOpen, setIsOpen] = useState(false);
  const [messages, setMessages] = useState<Message[]>([]);
  const [input, setInput] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const messagesEndRef = useRef<HTMLDivElement>(null);
  
  useEffect(() => {
    setMessages([
      { sender: 'ai', text: t('ai_greeting') }
    ]);
  }, [t]);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  useEffect(scrollToBottom, [messages]);

  const handleSend = async () => {
    if (input.trim() === '' || isLoading) return;

    const userMessage: Message = { sender: 'user', text: input };
    setMessages(prev => [...prev, userMessage]);
    setInput('');
    setIsLoading(true);

    try {
      const aiResponse = await askUstadzAI(input);
      const aiMessage: Message = { sender: 'ai', text: aiResponse };
      setMessages(prev => [...prev, aiMessage]);
    } catch (error) {
      const errorMessage: Message = { sender: 'ai', text: t('ai_error') };
      setMessages(prev => [...prev, errorMessage]);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <>
      <button
        onClick={() => setIsOpen(!isOpen)}
        className="fixed bottom-6 end-6 bg-brand-emerald-600 text-white w-16 h-16 rounded-full shadow-lg flex items-center justify-center hover:bg-brand-emerald-700 transition-transform transform hover:scale-110"
        aria-label={t('ai_open')}
      >
        <Icon className="w-8 h-8">
            <path d="M12.22 2h-4.44l-2 6-6 2 6 2 2 6 2-6 6-2-6-2z"/><path d="M20.91 14.65a2.43 2.43 0 0 0-2.26 2.26l.09.63a2.43 2.43 0 0 0 2.26 2.26l.63.09a2.43 2.43 0 0 0 2.26-2.26l-.09-.63a2.43 2.43 0 0 0-2.26-2.26Z"/><path d="M17 21.5a.5.5 0 1 0-1 0 .5.5 0 0 0 1 0Z"/>
        </Icon>
      </button>

      {isOpen && (
        <div className="fixed bottom-24 end-6 w-full max-w-sm h-[60vh] bg-white dark:bg-slate-800 rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-slate-200 dark:border-slate-700">
          <header className="p-4 bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <div className='flex items-center gap-3'>
                 <Icon className="w-7 h-7 text-brand-emerald-500">
                    <path d="M12.22 2h-4.44l-2 6-6 2 6 2 2 6 2-6 6-2-6-2z"/><path d="M20.91 14.65a2.43 2.43 0 0 0-2.26 2.26l.09.63a2.43 2.43 0 0 0 2.26 2.26l.63.09a2.43 2.43 0 0 0 2.26-2.26l-.09-.63a2.43 2.43 0 0 0-2.26-2.26Z"/><path d="M17 21.5a.5.5 0 1 0-1 0 .5.5 0 0 0 1 0Z"/>
                </Icon>
                <h3 className="font-bold text-lg text-slate-800 dark:text-white">{t('ai_title')}</h3>
            </div>
            <button onClick={() => setIsOpen(false)} className="text-slate-500 hover:text-slate-800 dark:hover:text-slate-200">
              <Icon className="w-6 h-6"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></Icon>
            </button>
          </header>

          <main className="flex-1 p-4 overflow-y-auto space-y-4">
            {messages.map((msg, index) => (
              <div key={index} className={`flex ${msg.sender === 'user' ? 'justify-end' : 'justify-start'}`}>
                <div className={`max-w-xs md:max-w-md lg:max-w-xs rounded-2xl px-4 py-3 ${
                    msg.sender === 'user' 
                    ? 'bg-brand-emerald-600 text-white rounded-ee-lg' 
                    : 'bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-100 rounded-es-lg'
                }`}>
                  <p className="text-sm">{msg.text}</p>
                </div>
              </div>
            ))}
             {isLoading && (
              <div className="flex justify-start">
                  <div className="bg-slate-200 dark:bg-slate-700 rounded-2xl px-4 py-3 rounded-es-lg">
                      <div className="flex items-center gap-2">
                        <span className="w-2 h-2 bg-slate-500 rounded-full animate-pulse delay-75"></span>
                        <span className="w-2 h-2 bg-slate-500 rounded-full animate-pulse delay-150"></span>
                        <span className="w-2 h-2 bg-slate-500 rounded-full animate-pulse delay-300"></span>
                      </div>
                  </div>
              </div>
            )}
            <div ref={messagesEndRef} />
          </main>

          <footer className="p-4 bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700">
            <div className="flex items-center gap-2">
              <input
                type="text"
                value={input}
                onChange={(e) => setInput(e.target.value)}
                onKeyPress={(e) => e.key === 'Enter' && handleSend()}
                placeholder={t('ai_placeholder')}
                className="flex-1 w-full px-4 py-2 rounded-full bg-slate-100 dark:bg-slate-700 border border-transparent focus:outline-none focus:ring-2 focus:ring-brand-emerald-500"
                disabled={isLoading}
              />
              <button onClick={handleSend} disabled={isLoading || input.trim() === ''} className="bg-brand-emerald-600 text-white p-3 rounded-full hover:bg-brand-emerald-700 disabled:bg-slate-400 disabled:cursor-not-allowed transition-colors">
                <Icon className="w-5 h-5 rtl:-scale-x-100"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></Icon>
              </button>
            </div>
          </footer>
        </div>
      )}
    </>
  );
};