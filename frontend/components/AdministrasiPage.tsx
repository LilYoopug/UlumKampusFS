import React, { useState } from 'react';
import { useLanguage } from '../contexts/LanguageContext';
import { User } from '../types';
import { PAYMENT_ITEMS_MOCK, PAYMENT_HISTORY_MOCK, PaymentMethod, PAYMENT_METHODS, PaymentItem, PaymentHistoryItem } from '../constants';



export const AdministrasiPage: React.FC<{ currentUser: User }> = ({ currentUser }) => {
  const { t } = useLanguage();
  const [loading, setLoading] = useState(false);
  const [paymentStatus, setPaymentStatus] = useState<'idle' | 'processing' | 'success' | 'error'>('idle');
  const [selectedPayment, setSelectedPayment] = useState<PaymentItem | null>(null);
  const [paymentMethod, setPaymentMethod] = useState<string>('bank_transfer');
  const [showReceipt, setShowReceipt] = useState<boolean>(false);
  const [receiptData, setReceiptData] = useState<any>(null);
  
  const [paymentItems, setPaymentItems] = useState<PaymentItem[]>(PAYMENT_ITEMS_MOCK);
  
  const [paymentHistory, setPaymentHistory] = useState<PaymentHistoryItem[]>(PAYMENT_HISTORY_MOCK);

  const handlePayment = (paymentItem: PaymentItem, method: string) => {
    setLoading(true);
    setPaymentStatus('processing');
    
    // Simulate payment processing
    setTimeout(() => {
      setPaymentStatus('success');
      setLoading(false);
      
      // Update payment status in the list
      setPaymentItems(prevItems =>
        prevItems.map(item =>
          item.id === paymentItem.id ? { ...item, status: 'paid' } : item
        )
      );
      
      // Create receipt data
      const newReceiptData = {
        id: `PAY-${Date.now()}`,
        paymentItem: paymentItem,
        amount: paymentItem.amount,
        date: new Date().toISOString(),
        method: method,
        studentName: currentUser.name,
        studentId: currentUser.studentId || currentUser.id || 'N/A',
        title: t(paymentItem.titleKey as any),
      };
      
      // Add to payment history
      setPaymentHistory(prevHistory => [
        {
          id: `${prevHistory.length + 1}`,
          title: t(paymentItem.titleKey as any),
          amount: paymentItem.amount,
          date: new Date().toISOString().split('T')[0],
          status: 'completed',
          paymentMethod: method, // Add payment method to history
        },
        ...prevHistory
      ]);
      
      // Set receipt data and show receipt modal
      setReceiptData(newReceiptData);
      setShowReceipt(true);
      
      // Reset after showing success message
      setTimeout(() => {
        setPaymentStatus('idle');
        setSelectedPayment(null);
      }, 3000);
    }, 1500);
  };

   const getStatusText = (status: string) => {
      switch (status) {
        case 'paid': return t('administrasi_paid');
        case 'unpaid': return t('administrasi_unpaid');
        case 'pending': return t('administrasi_pending');
        case 'completed': return t('administrasi_paid');
        case 'failed': return t('administrasi_payment_failed');
        default: return status;
      }
   };

    const getStatusClass = (status: string) => {
      switch (status) {
        case 'paid':
        case 'completed':
          return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        case 'unpaid':
          return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
        case 'pending':
          return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        default:
          return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
      }
    };

   return (
     <div className="space-y-8">
        <div>
          <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{t('administrasi_title')}</h1>
          <p className="text-slate-500 dark:text-slate-400 mt-1">{t('administrasi_subtitle')}</p>
        </div>

       {paymentStatus === 'processing' && (
         <div className="mb-6 p-4 bg-blue-50 dark:bg-blue-900/30 rounded-xl flex items-center">
           <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500 mr-3"></div>
           <p className="text-blue-700 dark:text-blue-300">
             {t('administrasi_make_payment')}...
           </p>
         </div>
       )}

       {paymentStatus === 'success' && (
         <div className="mb-6 p-4 bg-green-50 dark:bg-green-900/30 rounded-xl flex items-center">
           <svg className="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
             <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
           </svg>
           <p className="text-green-700 dark:text-green-300">
             {t('administrasi_payment_success')}
           </p>
         </div>
       )}

       {paymentStatus === 'error' && (
         <div className="mb-6 p-4 bg-red-50 dark:bg-red-900/30 rounded-xl flex items-center">
           <svg className="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
             <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
           </svg>
           <p className="text-red-700 dark:text-red-300">
             {t('administrasi_payment_failed')}
           </p>
         </div>
       )}

         {/* Payment Cards Section */}
         <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
           <h2 className="text-xl font-bold text-slate-800 dark:text-white mb-4">{t('sidebar_administrasi')}</h2>
           
           <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
           {paymentItems.map((item) => (
               <div 
                 key={item.id} 
                 className={`bg-white dark:bg-slate-800/50 rounded-2xl shadow-sm border ${
                   item.status === 'paid' 
                     ? 'border-green-500' 
                     : item.status === 'pending' 
                       ? 'border-yellow-500' 
                       : 'border-red-500'
                 }`}
               >
                 <div className="p-5 flex flex-col h-full">
                   <div className="flex justify-between items-start mb-3">
                     <h3 className="text-lg font-bold text-slate-800 dark:text-white">
                       {t(item.titleKey as any)}
                     </h3>
                     <span className={`px-3 py-1 rounded-full text-xs font-medium ${getStatusClass(item.status)}`}>
                       {getStatusText(item.status)}
                     </span>
                   </div>
                   
                   <p className="text-slate-600 dark:text-slate-300 mb-4 text-sm flex-grow">
                     {t(item.descriptionKey as any)}
                   </p>
                   
                    {item.dueDate && (
                      <div className="mb-4">
                        <span className="text-xs text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 px-3 py-1 rounded-full">
                          {t('calendar_due_date')} {item.dueDate}
                        </span>
                      </div>
                    )}
                    
                    <div className="mt-auto pt-4 border-t border-slate-200 dark:border-slate-700">
                     <div className="flex justify-between items-center mb-3">
                       <span className="text-sm text-slate-600 dark:text-slate-300">{t('administrasi_amount')}:</span>
                       <span className="text-lg font-bold text-slate-800 dark:text-white">
                         Rp {item.amount.toLocaleString('id-ID')}
                       </span>
                     </div>
                  
                     {item.status !== 'paid' && (
                       <button
                         onClick={() => {
                           setSelectedPayment(item);
                           setPaymentMethod('bank_transfer'); // default to bank transfer
                         }}
                         disabled={loading}
                         className={`w-full py-3 px-4 rounded-lg font-medium ${
                           loading
                             ? 'bg-slate-300 dark:bg-slate-600 cursor-not-allowed'
                             : 'bg-brand-emerald-600 hover:bg-brand-emerald-700 text-white'
                         }`}
                       >
                         <span className="flex items-center justify-center">
                           <svg className="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                             <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                           </svg>
                           {t('administrasi_make_payment')}
                         </span>
                       </button>
                     )}
                     
                     {item.status === 'paid' && (
                       <div className="w-full py-3 px-4 rounded-lg font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 text-center">
                         <span className="flex items-center justify-center">
                           <svg className="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                             <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
                           </svg>
                            {t('administrasi_paid')}
                          </span>
                       </div>
                     )}
                   </div>
                 </div>
               </div>
           ))}
         </div>
       </div>

         {/* Payment History Section */}
         <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
           <h2 className="text-xl font-bold text-slate-800 dark:text-white mb-4">{t('administrasi_payment_history')}</h2>
          
           {paymentHistory.length === 0 ? (
             <p className="text-slate-600 dark:text-slate-300 py-12 text-center italic">
               {t('administrasi_no_payment_history')}
             </p>
           ) : (
             <div className="overflow-x-auto">
               <table className="w-full text-sm text-left text-slate-500 dark:text-slate-400">
                 <thead className="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-300">
                   <tr>
                     <th scope="col" className="px-6 py-3">
                       {t('assignments_table_assignment')}
                     </th>
                     <th scope="col" className="px-6 py-3">
                       {t('administrasi_amount')}
                     </th>
                     <th scope="col" className="px-6 py-3">
                       {t('administrasi_receipt_method')}
                     </th>
                     <th scope="col" className="px-6 py-3">
                       {t('dashboard_calendar_date')}
                     </th>
                     <th scope="col" className="px-6 py-3">
                       {t('administrasi_payment_status')}
                     </th>
                   </tr>
                 </thead>
                 <tbody className="bg-white border-b dark:bg-slate-800 dark:border-slate-700">
                   {paymentHistory.map((historyItem) => (
                     <tr key={historyItem.id} className="bg-white border-b dark:bg-slate-800 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600/50">
                     <td scope="row" className="px-6 py-4 font-medium text-slate-900 whitespace-nowrap dark:text-white">
                       {historyItem.title}
                     </td>
                     <td className="px-6 py-4 font-bold text-brand-emerald-600 dark:text-brand-emerald-400">
                       Rp {historyItem.amount.toLocaleString('id-ID')}
                     </td>
                      <td className="px-6 py-4 text-slate-600 dark:text-slate-300">
                        {historyItem.paymentMethod ? t(PAYMENT_METHODS.find(m => m.id === historyItem.paymentMethod)?.nameKey as any) : '-'}
                      </td>
                     <td className="px-6 py-4 text-slate-600 dark:text-slate-300">
                       {historyItem.date}
                     </td>
                     <td className="px-6 py-4">
                       <span className={`px-3 py-1 rounded-full text-xs font-medium ${getStatusClass(historyItem.status)}`}>
                         {getStatusText(historyItem.status)}
                       </span>
                     </td>
                   </tr>
                 ))}
                 </tbody>
               </table>
             </div>
           )}
         </div>
         
          {/* Payment Method Selection Modal */}
 {selectedPayment && !showReceipt && !receiptData && (
            <div className="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-center z-50 p-4" onClick={() => setSelectedPayment(null)} role="dialog" aria-modal="true">
              <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-md" onClick={e => e.stopPropagation()}>
                <div className="p-6">
                  <div className="flex justify-between items-center mb-4">
                    <h3 className="text-lg font-semibold text-slate-800 dark:text-white">
                      {t('administrasi_make_payment')} - {t(selectedPayment.titleKey as any)}
                    </h3>
                    <button 
                      onClick={() => setSelectedPayment(null)}
                      className="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
                    >
                      <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
                      </svg>
                    </button>
                  </div>
                  
                  <p className="text-slate-600 dark:text-slate-300 text-sm mb-4">
                    {t(selectedPayment.descriptionKey as any)}
                  </p>
                  
                  <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
                    {t('administrasi_select_payment_method')}
                  </label>
                   <div className="grid grid-cols-2 gap-3 mb-4">
                     {PAYMENT_METHODS.map(method => (
                       <button
                         key={method.id}
                         type="button"
                         onClick={() => setPaymentMethod(method.id)}
                         className={`p-3 rounded-lg text-sm font-medium transition-colors ${
                           paymentMethod === method.id
                             ? 'bg-brand-emerald-100 dark:bg-brand-emerald-900/50 border border-brand-emerald-500 text-brand-emerald-700 dark:text-brand-emerald-300'
                             : 'bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600'
                         }`}
                       >
                         <div className="flex flex-col items-center">
                           <span className="text-xl">{method.icon}</span>
                           <span className="mt-1">{t(method.nameKey as any)}</span>
                         </div>
                       </button>
                     ))}
                   </div>
                   {selectedPayment.dueDate && (
                     <div className="flex justify-between items-center mb-3">
                       <span className="text-sm text-slate-600 dark:text-slate-300">{t('calendar_due_date')}:</span>
                       <span className="text-sm text-slate-800 dark:text-white">{selectedPayment.dueDate}</span>
                     </div>
                   )}
                   
                   <div className="flex gap-3">
                     <button
                       onClick={() => setSelectedPayment(null)}
                       className="flex-1 py-3 px-4 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-white rounded-lg font-semibold transition-colors">
                       {t('administrasi_close_receipt')}
                     </button>
                     <button
                       onClick={() => handlePayment(selectedPayment, paymentMethod)}
                       disabled={loading}
                       className={`flex-1 py-3 px-4 rounded-lg font-semibold transition-colors ${
                         loading
                           ? 'bg-slate-300 dark:bg-slate-600 cursor-not-allowed'
                           : 'bg-brand-emerald-600 hover:bg-brand-emerald-700 text-white'
                       }`}
                     >
                       {loading ? t('auth_loading') : t('administrasi_make_payment')}
                     </button>
                   </div>
                 </div>
               </div>
             </div>
           )}

        
        {/* Receipt Modal */}
         {receiptData && (
           <div className="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-center z-50 p-4" onClick={() => {
             setShowReceipt(false);
             setReceiptData(null);
           }} role="dialog" aria-modal="true">
             <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-md" onClick={e => e.stopPropagation()}>
               <div className="p-6">
                 <div className="flex justify-between items-center mb-4">
                   <h3 className="text-lg font-semibold text-slate-800 dark:text-white">
                     {t('administrasi_receipt_title')}
                   </h3>
                   <button 
                     onClick={() => {
                       setShowReceipt(false);
                       setReceiptData(null);
                     }}
                     className="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
                   >
                     <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                       <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
                     </svg>
                   </button>
                 </div>
                 
                 <div className="mb-4">
                   <div className="text-center mb-4">
                     <div className="text-brand-emerald-600 dark:text-brand-emerald-400 text-3xl mb-2">✓</div>
                     <h4 className="text-lg font-medium text-slate-800 dark:text-white">
                       {t('administrasi_receipt_subtitle')}
                     </h4>
                   </div>
                   
                   <div className="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4 space-y-3">
                     <div className="flex justify-between">
                       <span className="text-slate-600 dark:text-slate-300">{t('administrasi_receipt_id')}:</span>
                       <span className="font-medium text-slate-800 dark:text-white">{receiptData.id}</span>
                     </div>
                     <div className="flex justify-between">
                       <span className="text-slate-600 dark:text-slate-300">{t('administrasi_receipt_student')}:</span>
                       <span className="font-medium text-slate-800 dark:text-white">{receiptData.studentName}</span>
                     </div>
                     <div className="flex justify-between">
                       <span className="text-slate-600 dark:text-slate-300">{t('administrasi_receipt_nim')}:</span>
                       <span className="font-medium text-slate-800 dark:text-white">{receiptData.studentId}</span>
                     </div>
                     <div className="flex justify-between">
                       <span className="text-slate-600 dark:text-slate-300">{t('administrasi_payment_type')}:</span>
                       <span className="font-medium text-slate-800 dark:text-white">{receiptData.title}</span>
                     </div>
                     <div className="flex justify-between">
                       <span className="text-slate-600 dark:text-slate-300">{t('administrasi_receipt_method')}:</span>
                        <span className="font-medium text-slate-800 dark:text-white">
                          {t(PAYMENT_METHODS.find(m => m.id === receiptData.method)?.nameKey as any)}
                        </span>
                     </div>
                     <div className="flex justify-between">
                       <span className="text-slate-600 dark:text-slate-300">{t('dashboard_calendar_date')}:</span>
                       <span className="font-medium text-slate-800 dark:text-white">
                         {new Date(receiptData.date).toLocaleDateString('id-ID')}
                       </span>
                     </div>
                     <div className="flex justify-between pt-3 border-t border-slate-200 dark:border-slate-600">
                       <span className="text-slate-600 dark:text-slate-300 font-medium">{t('administrasi_amount')}:</span>
                       <span className="font-bold text-brand-emerald-600 dark:text-brand-emerald-400">
                         Rp {receiptData.amount.toLocaleString('id-ID')}
                       </span>
                     </div>
                   </div>
                 </div>
                 
                 <div className="flex gap-3">
                   <button
                     onClick={() => {
                       // In a real app, this would generate and download a PDF receipt
                       alert(t('administrasi_generate_receipt'));
                     }}
                     className="flex-1 py-3 px-4 bg-brand-emerald-600 hover:bg-brand-emerald-700 text-white rounded-lg font-semibold transition-colors"
                   >
                     {t('administrasi_generate_receipt')}
                   </button>
                   <button
                     onClick={() => {
                       setShowReceipt(false);
                       setReceiptData(null);
                     }}
                     className="flex-1 py-3 px-4 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-white rounded-lg font-semibold transition-colors"
                   >
                     {t('administrasi_close_receipt')}
                   </button>
                 </div>
               </div>
             </div>
           </div>
         )}
     </div>
   );
 };

