import React, { useState } from 'react';
import { useLanguage } from '@/contexts/LanguageContext';
import { User } from '@/types';

interface ManagementAdministrationPageProps {
  currentUser: User;
}

export const ManagementAdministrationPage: React.FC<ManagementAdministrationPageProps> = ({ currentUser }) => {
  const { t } = useLanguage();
  const [activeTab, setActiveTab] = useState('overview');
  const [showModal, setShowModal] = useState(false);
  const [selectedStudent, setSelectedStudent] = useState<any>(null);
  const [showReceipt, setShowReceipt] = useState(false);
  const [receiptData, setReceiptData] = useState<any>(null);
  const [paymentTypeList, setPaymentTypeList] = useState<any[]>([
    { id: 1, name: t('administrasi_registration_title'), description: t('administrasi_registration_desc'), amount: 5000000 },
    { id: 2, name: t('administrasi_semester_title'), description: t('administrasi_semester_desc'), amount: 3500000 },
    { id: 3, name: t('administrasi_exam_title'), description: t('administrasi_exam_desc'), amount: 250000 },
  ]);
  const [showAddForm, setShowAddForm] = useState(false);
  const [showEditForm, setShowEditForm] = useState(false);
  const [editingPayment, setEditingPayment] = useState<any>(null);
  const [newPayment, setNewPayment] = useState({ name: '', description: '', amount: '' });
  const [isEditingPayments, setIsEditingPayments] = useState(false);
  const [originalPaymentList, setOriginalPaymentList] = useState<any[]>([]);
  
  // Mock data for demonstration
  const [paymentStats] = useState({
    totalStudents: 1245,
    totalPayments: 2500000000, // in rupiah
    totalPaid: 2300000000,
    totalUnpaid: 200000000,
    pendingPayments: 42,
  });

  const [recentPayments] = useState([
    { id: 1, student: 'Ahmad Faris', type: 'Registration Payment', amount: 5000000, date: '2024-08-15', status: 'completed' },
    { id: 2, student: 'Siti Maryam', type: 'Semester Payment', amount: 3500000, date: '2024-08-14', status: 'completed' },
    { id: 3, student: 'Abdullah', type: 'Exam Payment', amount: 250000, date: '2024-08-13', status: 'completed' },
    { id: 4, student: 'Fatimah Az-Zahra', type: 'Registration Payment', amount: 5000000, date: '2024-08-12', status: 'pending' },
    { id: 5, student: 'Umar bin Khattab', type: 'Semester Payment', amount: 3500000, date: '2024-08-11', status: 'failed' },
  ]);

  const [paymentTypes] = useState([
    { id: 1, title: t('administrasi_registration_title'), total: 500000000, paid: 480000000, unpaid: 20000000 },
    { id: 2, title: t('administrasi_semester_title'), total: 1500000000, paid: 1450000000, unpaid: 50000000 },
    { id: 3, title: t('administrasi_exam_title'), total: 100000000, paid: 95000000, unpaid: 5000000 },
    { id: 4, title: 'Other Fees', total: 400000000, paid: 375000000, unpaid: 25000000 },
  ]);

   const [paymentMethods] = useState([
     { 
       id: 'bank_transfer', 
       name: t('administrasi_payment_method_bank_transfer'), 
        icon: (
          <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <path strokeLinecap="round" strokeLinejoin="round" d="M3 21h18M3 10h18M12 3l9 7-9 7-9-7 9-7M6 10v11M9 10v11M12 10v11M15 10v11M18 10v11" />
          </svg>
        ),
       count: 245 
     },
     { 
       id: 'credit_card', 
       name: t('administrasi_payment_method_credit_card'), 
       icon: (
         <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
           <rect x="3" y="6" width="18" height="12" rx="2" ry="2" />
           <path strokeLinecap="round" strokeLinejoin="round" d="M3 10h18" />
         </svg>
       ), 
       count: 120 
     },
     { 
       id: 'e_wallet', 
       name: t('administrasi_payment_method_e_wallet'), 
        icon: (
          <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <rect x="5" y="2" width="14" height="20" rx="2" ry="2" />
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 18h.01" />
          </svg>
        ),
       count: 180 
     },
     { 
       id: 'virtual_account', 
       name: t('administrasi_payment_method_virtual_account'), 
       icon: (
         <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
           <path strokeLinecap="round" strokeLinejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
         </svg>
       ), 
       count: 98 
     },
   ]);

  const renderContent = () => {
    switch (activeTab) {
      case 'overview':
        return (
          <div className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <div className="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-md">
                <div className="text-3xl font-bold text-brand-emerald-600 dark:text-brand-emerald-400">Rp {paymentStats.totalPayments.toLocaleString('id-ID')}</div>
                <div className="text-slate-600 dark:text-slate-300 mt-1">{t('management_admin_total_payments')}</div>
              </div>
              <div className="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-md">
                <div className="text-3xl font-bold text-green-600 dark:text-green-400">Rp {paymentStats.totalPaid.toLocaleString('id-ID')}</div>
                <div className="text-slate-600 dark:text-slate-300 mt-1">{t('management_admin_total_paid')}</div>
              </div>
              <div className="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-md">
                <div className="text-3xl font-bold text-red-600 dark:text-red-400">Rp {paymentStats.totalUnpaid.toLocaleString('id-ID')}</div>
                <div className="text-slate-600 dark:text-slate-300 mt-1">{t('management_admin_total_unpaid')}</div>
              </div>
              <div className="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-md">
                <div className="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{paymentStats.pendingPayments}</div>
                <div className="text-slate-600 dark:text-slate-300 mt-1">{t('management_admin_pending_payments')}</div>
              </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div className="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-md">
                <h3 className="text-lg font-semibold text-slate-800 dark:text-white mb-4">{t('management_admin_recent_payments')}</h3>
                <div className="overflow-x-auto">
                  <table className="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead className="bg-slate-50 dark:bg-slate-700/50">
                      <tr>
                        <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                          {t('management_admin_student')}
                        </th>
                        <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                          {t('management_admin_payment_type')}
                        </th>
                        <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                          {t('management_admin_amount')}
                        </th>
                        <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                          {t('management_admin_date')}
                        </th>
                        <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                          {t('management_admin_status')}
                        </th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-200 dark:divide-slate-700">
                      {recentPayments.map(payment => (
                        <tr key={payment.id} className="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                          <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800 dark:text-white">
                            {payment.student}
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                            {payment.type}
                          </td>
                           <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800 dark:text-white">
                             Rp {payment.amount.toLocaleString('id-ID')}
                           </td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                            {payment.date}
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm">
                              <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                                payment.status === 'completed' 
                                  ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' 
                                  : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
                              }`}>
                                {payment.status === 'completed' ? t('administrasi_lunas') : 
                                 t('administrasi_belum_lunas')}
                              </span>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>

              <div className="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-md">
                <h3 className="text-lg font-semibold text-slate-800 dark:text-white mb-4">{t('management_admin_payment_types')}</h3>
                <div className="space-y-4">
                  {paymentTypes.map(payment => (
                    <div key={payment.id} className="border-b border-slate-200 dark:border-slate-700 pb-4 last:border-0 last:pb-0">
                   <div className="flex justify-between items-center mb-2">
                     <div className="text-slate-800 dark:text-white font-medium">{payment.title}</div>
                     <div className="text-slate-600 dark:text-slate-300">Rp {payment.total.toLocaleString('id-ID')}</div>
                   </div>
                   <div className="flex justify-between text-sm">
                     <span className="text-green-600 dark:text-green-400">{t('management_admin_paid')}: Rp {payment.paid.toLocaleString('id-ID')}</span>
                     <span className="text-red-600 dark:text-red-400">{t('management_admin_unpaid')}: Rp {payment.unpaid.toLocaleString('id-ID')}</span>
                   </div>
                      <div className="mt-2 w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                        <div 
                          className="bg-green-500 h-2 rounded-full" 
                          style={{ width: `${(payment.paid / payment.total) * 100}%` }}
                        ></div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
            
            <div className="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-md">
              <h3 className="text-lg font-semibold text-slate-800 dark:text-white mb-4">{t('management_admin_payment_methods')}</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {paymentMethods.map(method => (
                  <div key={method.id} className="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                    <div className="p-3 rounded-full bg-white dark:bg-slate-600 text-brand-emerald-500 flex items-center justify-center w-12 h-12">
                      <div className="w-6 h-6 flex items-center justify-center">
                        {method.icon}
                      </div>
                    </div>
                    <div>
                      <div className="text-lg font-medium text-slate-800 dark:text-white">{method.name}</div>
                      <div className="text-slate-600 dark:text-slate-300 text-sm">{method.count} {t('management_admin_transactions')}</div>
                    </div>
                  </div>
                ))}
               </div>
            </div>
          </div>
        );
       case 'payment-management':
         // Function to handle view button click
         const handleViewClick = (i: number) => {
           const studentData = {
             id: `UC202400${i+1}`,
             name: ['Ahmad Faris', 'Siti Maryam', 'Abdullah', 'Fatimah Az-Zahra', 'Umar bin Khattab'][i % 5],
             totalAmount: Math.floor(Math.random() * 5000000 + 250000),
             latestTransaction: ['2024-12-10', '2024-12-08', '2024-12-05', '2024-12-01', '2024-11-28'][i % 5],
             status: i % 3 === 0 ? 'paid' : 'unpaid',
             paymentList: [
               { id: 1, type: t('administrasi_registration_title'), amount: 5000000, status: i % 2 === 0 ? 'paid' : 'unpaid', date: '2024-09-15' },
               { id: 2, type: t('administrasi_semester_title'), amount: 3500000, status: i % 2 === 0 ? 'paid' : 'unpaid', date: '2024-09-30' },
               { id: 3, type: t('administrasi_exam_title'), amount: 250000, status: 'paid', date: '2024-10-10' },
               { id: 4, type: t('management_admin_other_fees'), amount: 1000000, status: i % 2 === 0 ? 'paid' : 'unpaid', date: '2024-10-15' },
             ]
           };
           setSelectedStudent(studentData);
           setShowModal(true);
         };

         return (
           <div className="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-md">
             <h3 className="text-lg font-semibold text-slate-800 dark:text-white mb-4">{t('management_admin_payment_management')}</h3>
             <div className="overflow-x-auto">
               <table className="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                 <thead className="bg-slate-50 dark:bg-slate-700/50">
                   <tr>
                     <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                       {t('management_admin_student_id')}
                     </th>
                     <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                       {t('management_admin_student_name')}
                     </th>
                     <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                       {t('management_admin_amount')}
                     </th>
                     <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                       {t('management_admin_latest_transaction')}
                     </th>
                     <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                       {t('management_admin_status')}
                     </th>
                     <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                       {t('management_admin_actions')}
                     </th>
                   </tr>
                 </thead>
                 <tbody className="divide-y divide-slate-200 dark:divide-slate-700">
                   {Array.from({ length: 10 }, (_, i) => (
                     <tr key={i} className="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                       <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800 dark:text-white">
                         UC202400{i+1}
                       </td>
                       <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                         {['Ahmad Faris', 'Siti Maryam', 'Abdullah', 'Fatimah Az-Zahra', 'Umar bin Khattab'][i % 5]}
                       </td>
                       <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800 dark:text-white">
                         Rp {Math.floor(Math.random() * 5000000 + 250000).toLocaleString('id-ID')}
                       </td>
                       <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                         {['2024-12-10', '2024-12-08', '2024-12-05', '2024-12-01', '2024-11-28'][i % 5]}
                       </td>
                       <td className="px-6 py-4 whitespace-nowrap text-sm">
                          <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                            i % 2 === 0
                              ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' 
                              : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
                          }`}>
                            {i % 2 === 0 ? t('administrasi_lunas') : t('administrasi_belum_lunas')}
                          </span>
                       </td>
                       <td className="px-6 py-4 whitespace-nowrap text-sm">
                         <button 
                           onClick={() => handleViewClick(i)}
                           className="text-brand-emerald-600 hover:text-brand-emerald-900 dark:text-brand-emerald-400 dark:hover:text-brand-emerald-300"
                         >
                           {t('management_admin_view')}
                         </button>
                       </td>
                     </tr>
                   ))}
                 </tbody>
               </table>
             </div>

             {/* Modal Popup */}
             {showModal && selectedStudent && (
               <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
                 <div className="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                   <div className="p-6">
                     <div className="flex justify-between items-center mb-4">
                       <h3 className="text-xl font-bold text-slate-800 dark:text-white">
                         {t('management_admin_student_payment_details')} - {selectedStudent.name}
                       </h3>
                       <button 
                         onClick={() => setShowModal(false)}
                         className="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
                       >
                         <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                           <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
                         </svg>
                       </button>
                     </div>
                     
                     <div className="mb-6">
                       <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                         <div className="bg-slate-50 dark:bg-slate-700/50 p-4 rounded-lg">
                           <div className="text-sm text-slate-500 dark:text-slate-400">{t('management_admin_student_id')}</div>
                           <div className="font-medium text-slate-800 dark:text-white">{selectedStudent.id}</div>
                         </div>
                         <div className="bg-slate-50 dark:bg-slate-700/50 p-4 rounded-lg">
                           <div className="text-sm text-slate-500 dark:text-slate-400">{t('management_admin_total_payment')}</div>
                           <div className="font-medium text-slate-800 dark:text-white">Rp {selectedStudent.totalAmount.toLocaleString('id-ID')}</div>
                         </div>
                         <div className="bg-slate-50 dark:bg-slate-700/50 p-4 rounded-lg">
                           <div className="text-sm text-slate-500 dark:text-slate-400">{t('management_admin_latest_transaction')}</div>
                           <div className="font-medium text-slate-800 dark:text-white">{selectedStudent.latestTransaction}</div>
                         </div>
                        </div>
                        
                        <div className="flex justify-between items-center mb-3">
                          <h4 className="text-lg font-semibold text-slate-800 dark:text-white">{t('management_admin_payment_list')}</h4>
                          <div className="relative w-32 h-8 flex items-center justify-end">
                            <div className={`absolute flex space-x-1 right-0 transition-opacity duration-200 ${isEditingPayments ? 'opacity-100' : 'opacity-0 pointer-events-none'}`}>
                              <button 
                                onClick={() => {
                                  // Cancel edit mode - restore original payment list
                                  const updatedStudent = {
                                    ...selectedStudent,
                                    paymentList: originalPaymentList
                                  };
                                  setSelectedStudent(updatedStudent);
                                  setIsEditingPayments(false);
                                  setOriginalPaymentList([]);
                                }}
                                className="px-3 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-white rounded-lg font-medium transition-colors"
                              >
                                {t('management_admin_cancel')}
                              </button>
                              <button 
                                onClick={() => setIsEditingPayments(false)}
                                className="px-3 py-2 bg-brand-emerald-600 hover:bg-brand-emerald-700 text-white rounded-lg font-medium transition-colors"
                              >
                                {t('management_admin_save')}
                              </button>
                            </div>
                            <div className={`absolute flex space-x-1 right-0 transition-opacity duration-200 ${isEditingPayments ? 'opacity-0 pointer-events-none' : 'opacity-100'}`}>
                              <button 
                                onClick={() => {
                                  // Save original payment list before entering edit mode
                                  setOriginalPaymentList([...selectedStudent.paymentList]);
                                  setIsEditingPayments(true);
                                }}
                                className="px-3 py-2 bg-brand-emerald-600 hover:bg-brand-emerald-700 text-white rounded-lg font-medium transition-colors"
                              >
                                {t('management_admin_edit')}
                              </button>
                            </div>
                          </div>
                        </div>
                       
                       <div className="overflow-x-auto">
                         <table className="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                           <thead className="bg-slate-50 dark:bg-slate-700/50">
                             <tr>
                               <th scope="col" className="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                                 {t('management_admin_payment_type')}
                               </th>
                               <th scope="col" className="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                                 {t('management_admin_amount')}
                               </th>
                               <th scope="col" className="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                                 {t('management_admin_date')}
                               </th>
                               <th scope="col" className="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                                 {t('management_admin_status')}
                               </th>
                               <th scope="col" className="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                                 {t('management_admin_receipt')}
                               </th>
                             </tr>
                           </thead>
                           <tbody className="divide-y divide-slate-200 dark:divide-slate-700">
                             {selectedStudent.paymentList.map((payment: any, idx: number) => (
                               <tr key={idx} className="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                 <td className="px-4 py-3 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                                   {payment.type}
                                 </td>
                                 <td className="px-4 py-3 whitespace-nowrap text-sm font-medium text-slate-800 dark:text-white">
                                   Rp {payment.amount.toLocaleString('id-ID')}
                                 </td>
                                 <td className="px-4 py-3 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                                   {payment.date}
                                 </td>
                          <td className="px-4 py-3 whitespace-nowrap text-sm">
                             {isEditingPayments ? (
                               <div className="flex items-center">
                                 <button
                                   onClick={() => {
                                     // Toggle payment status
                                     const updatedPaymentList = selectedStudent.paymentList.map((p: any) => 
                                       p.id === payment.id 
                                         ? { ...p, status: p.status === 'paid' ? 'unpaid' : 'paid' }
                                         : p
                                     );
                                     const updatedStudent = {
                                       ...selectedStudent,
                                       paymentList: updatedPaymentList
                                     };
                                     setSelectedStudent(updatedStudent);
                                   }}
                                   className={`px-3 py-1 rounded text-sm font-medium ${
                                     payment.status === 'paid'
                                       ? 'bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-300 dark:hover:bg-green-800/40' 
                                       : 'bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-300 dark:hover:bg-red-800/40'
                                   }`}
                                 >
                                   {payment.status === 'paid' ? t('administrasi_lunas') : t('administrasi_belum_lunas')}
                                 </button>
                               </div>
                             ) : (
                               <div className="flex items-center">
                                 <span className={`px-3 py-1 rounded text-sm font-medium ${
                                   payment.status === 'paid'
                                     ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' 
                                     : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
                                 }`}>
                                   {payment.status === 'paid' ? t('administrasi_lunas') : t('administrasi_belum_lunas')}
                                 </span>
                               </div>
                             )}
                          </td>
                                  <td className="px-4 py-3 whitespace-nowrap text-sm">
                                    {isEditingPayments ? (
                                      <div className="flex items-center">
                                        <span className="text-slate-400 dark:text-slate-500 text-sm">
                                           {payment.status === 'paid' ? t('management_admin_view_receipt') : t('administrasi_belum_lunas')}
                                        </span>
                                      </div>
                                    ) : (
                                      payment.status === 'paid' ? (
                                        <div className="flex items-center">
                                          <button
                                            onClick={() => {
                                              setReceiptData({
                                                id: `RECEIPT-${selectedStudent.id}-${idx}`,
                                                title: payment.type,
                                                amount: payment.amount,
                                                date: payment.date,
                                                studentName: selectedStudent.name,
                                                studentId: selectedStudent.id,
                                                method: 'bank_transfer', // Default method for admin view
                                              });
                                              setShowReceipt(true);
                                            }}
                                            className="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm"
                                          >
                                            {t('management_admin_view_receipt')}
                                          </button>
                                        </div>
                                      ) : (
                                        <div className="flex items-center">
                                          <span className="text-slate-400 dark:text-slate-500 text-sm">
                                            {t('management_admin_not_paid')}
                                          </span>
                                        </div>
                                      )
                                    )}
                                  </td>
                               </tr>
                             ))}
                           </tbody>
                         </table>
                       </div>
                     </div>
                     
                     <div className="flex justify-end">
                       <button
                         onClick={() => setShowModal(false)}
                         className="px-4 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-white rounded-lg transition-colors"
                       >
                         {t('management_admin_close')}
                       </button>
                     </div>
                   </div>
                 </div>
               </div>
             )}

             {/* Receipt Modal */}
             {showReceipt && receiptData && (
               <div className="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
                 <div className="bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
                   <div className="p-6">
                     <div className="flex justify-between items-center mb-6">
                       <h3 className="text-xl font-bold text-slate-800 dark:text-white">
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
                     
                     <div className="mb-6">
                       <div className="text-center mb-4">
                         <div className="text-brand-emerald-600 dark:text-brand-emerald-400 text-4xl mb-2">✓</div>
                         <h4 className="text-lg font-semibold text-slate-800 dark:text-white">
                           {t('administrasi_receipt_subtitle')}
                         </h4>
                       </div>
                       
                       <div className="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-4 space-y-3">
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
                             {t('administrasi_payment_method_bank_transfer')}
                           </span>
                         </div>
                         <div className="flex justify-between">
                           <span className="text-slate-600 dark:text-slate-300">{t('dashboard_calendar_date')}:</span>
                           <span className="font-medium text-slate-800 dark:text-white">
                             {new Date(receiptData.date).toLocaleDateString('id-ID')}
                           </span>
                         </div>
                         <div className="flex justify-between pt-3 border-t border-slate-200 dark:border-slate-600">
                           <span className="text-slate-600 dark:text-slate-300 font-semibold">{t('administrasi_amount')}:</span>
                           <span className="font-bold text-lg text-brand-emerald-600 dark:text-brand-emerald-400">
                             Rp {receiptData.amount.toLocaleString('id-ID')}
                           </span>
                         </div>
                       </div>
                     </div>
                     
                     <div className="flex flex-col sm:flex-row gap-3">
                       <button
                         onClick={() => {
                           // In a real app, this would generate and download a PDF receipt
                           alert(t('administrasi_generate_receipt'));
                         }}
                         className="flex-1 py-2 px-4 bg-brand-emerald-600 hover:bg-brand-emerald-700 text-white rounded-lg font-medium transition-colors"
                       >
                         {t('administrasi_generate_receipt')}
                       </button>
                       <button
                         onClick={() => {
                           setShowReceipt(false);
                           setReceiptData(null);
                         }}
                         className="flex-1 py-2 px-4 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-white rounded-lg font-medium transition-colors"
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
       case 'payment-types':
         const handleAddPayment = () => {
           if (newPayment.name && newPayment.description && newPayment.amount) {
             const payment = {
               id: paymentTypeList.length + 1,
               name: newPayment.name,
               description: newPayment.description,
               amount: parseInt(newPayment.amount)
             };
             setPaymentTypeList([...paymentTypeList, payment]);
             setNewPayment({ name: '', description: '', amount: '' });
             setShowAddForm(false);
           }
         };

         const handleDeletePayment = (id: number) => {
           setPaymentTypeList(paymentTypeList.filter(payment => payment.id !== id));
         };

          return (
            <div className="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-md">
              <div className="flex justify-between items-center mb-6">
                <h3 className="text-lg font-semibold text-slate-800 dark:text-white">{t('management_admin_fee_types')}</h3>
                <button 
                  onClick={() => setShowAddForm(true)}
                  className="px-4 py-2 bg-brand-emerald-600 hover:bg-brand-emerald-700 text-white rounded-lg transition-colors"
                >
                  {t('management_admin_add_payment')}
                </button>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {paymentTypeList.map((payment) => (
                  <div 
                    key={payment.id} 
                    className="bg-white dark:bg-slate-800/50 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700"
                  >
                    <div className="p-4 flex flex-col h-full">
                      <div className="flex justify-between items-start mb-2">
                        <h3 className="text-base font-semibold text-slate-800 dark:text-white">
                          {payment.name}
                        </h3>
                      </div>
                      
                      <p className="text-slate-600 dark:text-slate-300 mb-3 text-sm flex-grow">
                        {payment.description}
                      </p>
                      
                      <div className="mt-auto pt-3 border-t border-slate-200 dark:border-slate-700">
                        <div className="flex justify-between items-center mb-3">
                          <span className="text-xs text-slate-600 dark:text-slate-300">{t('management_admin_amount')}:</span>
                          <span className="text-sm font-bold text-slate-800 dark:text-white">
                            Rp {payment.amount.toLocaleString('id-ID')}
                          </span>
                        </div>
                        
                        <div className="flex space-x-2">
                          <button
                            onClick={() => {
                              setEditingPayment(payment);
                              setNewPayment({ name: payment.name, description: payment.description, amount: payment.amount.toString() });
                              setShowEditForm(true);
                            }}
                            className="flex-1 py-2 px-3 bg-brand-emerald-100 hover:bg-brand-emerald-200 dark:bg-brand-emerald-900/50 dark:hover:bg-brand-emerald-800/70 text-brand-emerald-700 dark:text-brand-emerald-200 rounded-lg font-medium text-sm transition-colors"
                          >
                            {t('management_admin_edit')}
                          </button>
                          <button
                            onClick={() => handleDeletePayment(payment.id)}
                            className="flex-1 py-2 px-3 bg-red-100 hover:bg-red-200 dark:bg-red-900/50 dark:hover:bg-red-800/70 text-red-700 dark:text-red-200 rounded-lg font-medium text-sm transition-colors"
                          >
                            {t('management_admin_delete')}
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                ))}
              </div>

              {/* Add Payment Modal */}
             {showAddForm && (
               <div className="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
                 <div className="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-md">
                   <div className="p-6">
                     <div className="flex justify-between items-center mb-4">
                       <h3 className="text-lg font-semibold text-slate-800 dark:text-white">
                         {t('management_admin_add_new_payment')}
                       </h3>
                       <button 
                         onClick={() => {
                           setShowAddForm(false);
                           setNewPayment({ name: '', description: '', amount: '' });
                         }}
                         className="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
                       >
                         <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                           <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
                         </svg>
                       </button>
                     </div>

                     <div className="space-y-4">
                       <div>
                         <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{t('management_admin_payment_name')}</label>
                         <input
                           type="text"
                           value={newPayment.name}
                           onChange={(e) => setNewPayment({...newPayment, name: e.target.value})}
                           className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white"
                           placeholder={t('management_admin_payment_name_placeholder')}
                         />
                       </div>
                       <div>
                         <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{t('management_admin_payment_description')}</label>
                         <input
                           type="text"
                           value={newPayment.description}
                           onChange={(e) => setNewPayment({...newPayment, description: e.target.value})}
                           className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white"
                           placeholder={t('management_admin_payment_description_placeholder')}
                         />
                       </div>
                       <div>
                         <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{t('management_admin_payment_amount')}</label>
                         <input
                           type="number"
                           value={newPayment.amount}
                           onChange={(e) => setNewPayment({...newPayment, amount: e.target.value})}
                           className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white"
                           placeholder={t('management_admin_payment_amount_placeholder')}
                         />
                       </div>
                       <div className="flex space-x-2">
                         <button
                           onClick={() => {
                             if (newPayment.name && newPayment.description && newPayment.amount) {
                               const payment = {
                                 id: paymentTypeList.length + 1,
                                 name: newPayment.name,
                                 description: newPayment.description,
                                 amount: parseInt(newPayment.amount)
                               };
                               setPaymentTypeList([...paymentTypeList, payment]);
                               setShowAddForm(false);
                               setNewPayment({ name: '', description: '', amount: '' });
                             }
                           }}
                           className="flex-1 px-4 py-2 bg-brand-emerald-600 hover:bg-brand-emerald-700 text-white rounded-lg transition-colors"
                         >
                           {t('management_admin_save_payment')}
                         </button>
                         <button
                           onClick={() => {
                             setShowAddForm(false);
                             setNewPayment({ name: '', description: '', amount: '' });
                           }}
                           className="flex-1 px-4 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-white rounded-lg transition-colors"
                         >
                           {t('management_admin_cancel')}
                         </button>
                       </div>
                     </div>
                   </div>
                 </div>
               </div>
             )}

             {/* Edit Payment Modal */}
             {showEditForm && editingPayment && (
               <div className="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
                 <div className="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-md">
                   <div className="p-6">
                     <div className="flex justify-between items-center mb-4">
                       <h3 className="text-lg font-semibold text-slate-800 dark:text-white">
                         {t('management_admin_edit_payment')}
                       </h3>
                       <button 
                         onClick={() => {
                           setShowEditForm(false);
                           setEditingPayment(null);
                           setNewPayment({ name: '', description: '', amount: '' });
                         }}
                         className="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
                       >
                         <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                           <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
                         </svg>
                       </button>
                     </div>

                     <div className="space-y-4">
                       <div>
                         <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{t('management_admin_payment_name')}</label>
                         <input
                           type="text"
                           value={newPayment.name}
                           onChange={(e) => setNewPayment({...newPayment, name: e.target.value})}
                           className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white"
                           placeholder={t('management_admin_payment_name_placeholder')}
                         />
                       </div>
                       <div>
                         <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{t('management_admin_payment_description')}</label>
                         <input
                           type="text"
                           value={newPayment.description}
                           onChange={(e) => setNewPayment({...newPayment, description: e.target.value})}
                           className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white"
                           placeholder={t('management_admin_payment_description_placeholder')}
                         />
                       </div>
                       <div>
                         <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{t('management_admin_payment_amount')}</label>
                         <input
                           type="number"
                           value={newPayment.amount}
                           onChange={(e) => setNewPayment({...newPayment, amount: e.target.value})}
                           className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white"
                           placeholder={t('management_admin_payment_amount_placeholder')}
                         />
                       </div>
                       <div className="flex space-x-2">
                          <button
                            onClick={() => {
                              if (newPayment.name && newPayment.amount) {
                                // Check if this is for adding a student payment vs editing a payment type
                                if (editingPayment.studentId) {
                                  // This is for adding a payment to a specific student
                                  const newStudentPayment = {
                                    id: selectedStudent.paymentList.length + 1,
                                    type: newPayment.name,
                                    description: newPayment.description,
                                    amount: parseInt(newPayment.amount),
                                    status: 'unpaid', // New payments start as unpaid
                                    date: new Date().toISOString().split('T')[0] // Today's date
                                  };
                                  
                                  // Update the selected student's payment list
                                  const updatedStudent = {
                                    ...selectedStudent,
                                    paymentList: [...selectedStudent.paymentList, newStudentPayment]
                                  };
                                  setSelectedStudent(updatedStudent);
                                } else {
                                  // This is for editing a payment type in the global list
                                  const updatedList = paymentTypeList.map(p => 
                                    p.id === editingPayment.id 
                                      ? { ...p, name: newPayment.name, description: newPayment.description, amount: parseInt(newPayment.amount) }
                                      : p
                                  );
                                  setPaymentTypeList(updatedList);
                                }
                                setShowEditForm(false);
                                setEditingPayment(null);
                                setNewPayment({ name: '', description: '', amount: '' });
                              }
                            }}
                            className="flex-1 px-4 py-2 bg-brand-emerald-600 hover:bg-brand-emerald-700 text-white rounded-lg transition-colors"
                          >
                            {t('management_admin_save_changes')}
                          </button>
                         <button
                           onClick={() => {
                             setShowEditForm(false);
                             setEditingPayment(null);
                             setNewPayment({ name: '', description: '', amount: '' });
                           }}
                           className="flex-1 px-4 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-white rounded-lg transition-colors"
                         >
                           {t('management_admin_cancel')}
                         </button>
                       </div>
                     </div>
                   </div>
                 </div>
               </div>
             )}
           </div>
         );
      default:
        return (
          <div className="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-md">
            <h3 className="text-lg font-semibold text-slate-800 dark:text-white mb-4">{t('management_admin_overview')}</h3>
            <p className="text-slate-600 dark:text-slate-300">{t('page_content_placeholder')}</p>
          </div>
        );
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-slate-800 dark:text-white">{t('management_admin_title')}</h1>
        <p className="text-slate-500 dark:text-slate-400 mt-1">{t('management_admin_subtitle')}</p>
      </div>

       <div className="border-b border-slate-200 dark:border-slate-700">
         <nav className="-mb-px flex space-x-8 overflow-x-auto">
           <button
             onClick={() => setActiveTab('overview')}
             className={`whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm ${
               activeTab === 'overview'
                 ? 'border-brand-emerald-500 text-brand-emerald-600 dark:text-brand-emerald-400 dark:border-brand-emerald-400'
                 : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300'
             }`}
           >
             {t('management_admin_overview')}
           </button>
           <button
             onClick={() => setActiveTab('payment-management')}
             className={`whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm ${
               activeTab === 'payment-management'
                 ? 'border-brand-emerald-500 text-brand-emerald-600 dark:text-brand-emerald-400 dark:border-brand-emerald-400'
                 : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300'
             }`}
           >
             {t('management_admin_payment_management')}
           </button>
           <button
             onClick={() => setActiveTab('payment-types')}
             className={`whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm ${
               activeTab === 'payment-types'
                 ? 'border-brand-emerald-500 text-brand-emerald-600 dark:text-brand-emerald-400 dark:border-brand-emerald-400'
                 : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300'
             }`}
           >
              {t('management_admin_fee_types')}
           </button>
         </nav>
       </div>

      {renderContent()}
    </div>
  );
};