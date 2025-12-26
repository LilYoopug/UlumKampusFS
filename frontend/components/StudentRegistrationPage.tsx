import React, { useState } from 'react';
import { Icon } from './Icon';
import { useLanguage } from '../contexts/LanguageContext';

interface StudentRegistration {
  id: string;
  name: string;
  email: string;
  studentId: string;
  program: string;
  registrationDate: string;
  status: 'pending' | 'approved' | 'rejected';
  // Registration form fields
  nik: string;
  fullName: string;
  gender: string;
  birthDate: string;
  birthPlace: string;
  religion: string;
  address: string;
  phone: string;
  parentName: string;
  parentOccupation: string;
  parentPhone: string;
  educationLevel: string;
  educationMajor: string;
  educationName: string;
  educationAddress: string;
  educationGraduationYear: string;
  selectedProgram: string;
  selectedFaculty: string;
  // Account information
  accountName: string;
  accountEmail: string;
  accountPhone: string;
  accountCreatedDate: string;
  // Payment information
  paymentMethod: string;
  paymentAmount: string;
  paymentStatus: 'completed' | 'pending' | 'failed';
}

export const StudentRegistrationPage: React.FC = () => {
  const { t } = useLanguage();
  const [registrations, setRegistrations] = useState<StudentRegistration[]>([
    {
      id: '1',
      name: 'John Doe',
      email: 'john.doe@example.com',
      studentId: 'STD001',
      program: 'Computer Science',
      registrationDate: '2023-05-15',
      status: 'pending',
      nik: '1234567890123456',
      fullName: 'John Doe',
      gender: 'Laki-laki',
      birthDate: '2000-01-15',
      birthPlace: 'Jakarta',
      religion: 'Islam',
      address: 'Jl. Merdeka No. 123, Jakarta',
      phone: '081234567890',
      parentName: 'Robert Doe',
      parentOccupation: 'Pegawai Swasta',
      parentPhone: '081234567891',
      educationLevel: 'SMA',
      educationMajor: 'IPA',
      educationName: 'SMA Negeri 1 Jakarta',
      educationAddress: 'Jl. Pendidikan No. 45, Jakarta',
      educationGraduationYear: '2022',
      selectedProgram: 'Teknik Informatika',
      selectedFaculty: 'Fakultas Teknik',
      accountName: 'John Doe',
      accountEmail: 'john.doe@example.com',
      accountPhone: '081234567890',
      accountCreatedDate: '2023-05-10',
      paymentMethod: 'Transfer Bank',
      paymentAmount: 'Rp 500.000',
      paymentStatus: 'pending'
    },
    {
      id: '2',
      name: 'Jane Smith',
      email: 'jane.smith@example.com',
      studentId: 'STD002',
      program: 'Mathematics',
      registrationDate: '2023-05-16',
      status: 'pending',
      nik: '9876543210987654',
      fullName: 'Jane Smith',
      gender: 'Perempuan',
      birthDate: '2001-03-20',
      birthPlace: 'Bandung',
      religion: 'Islam',
      address: 'Jl. Pahlawan No. 456, Bandung',
      phone: '081234567892',
      parentName: 'Michael Smith',
      parentOccupation: 'Guru',
      parentPhone: '081234567893',
      educationLevel: 'SMA',
      educationMajor: 'IPS',
      educationName: 'SMA Negeri 2 Bandung',
      educationAddress: 'Jl. Pendidikan No. 78, Bandung',
      educationGraduationYear: '2022',
      selectedProgram: 'Matematika',
      selectedFaculty: 'Fakultas Sains',
      accountName: 'Jane Smith',
      accountEmail: 'jane.smith@example.com',
      accountPhone: '081234567892',
      accountCreatedDate: '2023-05-11',
      paymentMethod: 'Transfer Bank',
      paymentAmount: 'Rp 500.000',
      paymentStatus: 'completed'
    },
    {
      id: '3',
      name: 'Robert Johnson',
      email: 'robert.j@example.com',
      studentId: 'STD003',
      program: 'Physics',
      registrationDate: '2023-05-17',
      status: 'approved',
      nik: '1122334455667788',
      fullName: 'Robert Johnson',
      gender: 'Laki-laki',
      birthDate: '1999-07-10',
      birthPlace: 'Surabaya',
      religion: 'Kristen',
      address: 'Jl. Kenangan No. 789, Surabaya',
      phone: '081234567894',
      parentName: 'William Johnson',
      parentOccupation: 'PNS',
      parentPhone: '081234567895',
      educationLevel: 'SMA',
      educationMajor: 'IPA',
      educationName: 'SMA Negeri 3 Surabaya',
      educationAddress: 'Jl. Pendidikan No. 101, Surabaya',
      educationGraduationYear: '2021',
      selectedProgram: 'Fisika',
      selectedFaculty: 'Fakultas Sains',
      accountName: 'Robert Johnson',
      accountEmail: 'robert.j@example.com',
      accountPhone: '081234567894',
      accountCreatedDate: '2023-05-12',
      paymentMethod: 'Transfer Bank',
      paymentAmount: 'Rp 500.000',
      paymentStatus: 'failed'
    }
  ]);

  const [activeTab, setActiveTab] = useState<'all' | 'pending' | 'approved' | 'rejected'>('all');
  const [showDetailModal, setShowDetailModal] = useState(false);
  const [selectedStudent, setSelectedStudent] = useState<StudentRegistration | null>(null);

  const handleApprove = (id: string) => {
    setRegistrations(prev => 
       prev.map(reg => 
         reg.id === id ? { ...reg, paymentStatus: 'completed' } : reg
       )
    );
    setShowDetailModal(false);
  };

  const handleReject = (id: string) => {
    setRegistrations(prev => 
       prev.map(reg => 
         reg.id === id ? { ...reg, paymentStatus: 'failed' } : reg
       )
    );
    setShowDetailModal(false);
  };

  const handleViewDetails = (student: StudentRegistration) => {
    setSelectedStudent(student);
    setShowDetailModal(true);
  };

  const pendingRegistrations = registrations.filter(reg => reg.paymentStatus === 'pending');

  // Stat card component matching the dashboard style
  const StatCard: React.FC<{ value: string, label: string, icon: React.ReactNode }> = ({ value, label, icon }) => (
    <div className="bg-white dark:bg-slate-800/50 p-5 rounded-2xl shadow-md flex items-center space-x-4 rtl:space-x-reverse">
      <div className="p-3 rounded-full bg-slate-100 dark:bg-slate-700">
        {icon}
      </div>
      <div className="text-start">
        <p className="text-2xl font-bold text-slate-800 dark:text-white">{value}</p>
        <p className="text-slate-500 dark:text-slate-400 text-sm font-medium">{label}</p>
      </div>
    </div>
  );

  return (
    <div className="space-y-8">
      <div className="max-w-6xl mx-auto">
        <h1 className="text-3xl font-bold text-slate-800 dark:text-white">
          {t('student_registration_management')}
        </h1>
        <p className="text-slate-500 dark:text-slate-400 mt-1">
          {t('student_registration_manage_subtitle')}
        </p>

         {/* Stats Cards */}
         <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
           <StatCard 
             icon={<Icon className="w-8 h-8 text-blue-500"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></Icon>} 
             value={registrations.length.toString()}
             label={t('student_registration_total_applicants')}
           />
           <StatCard 
             icon={<Icon className="w-8 h-8 text-yellow-500"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></Icon>} 
             value={pendingRegistrations.length.toString()} 
             label={t('student_registration_pending')}
           />
           <StatCard 
             icon={<Icon className="w-8 h-8 text-green-500"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></Icon>} 
              value={registrations.filter(r => r.paymentStatus === 'completed').length.toString()}
             label={t('student_registration_approved')}
           />
           <StatCard 
             icon={<Icon className="w-8 h-8 text-red-500"><circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="2"/><path d="m15 9-6 6m0-6 6 6" stroke="currentColor" strokeWidth="2" strokeLinecap="round"/></Icon>} 
              value={registrations.filter(r => r.paymentStatus === 'failed').length.toString()}
             label={t('student_registration_rejected')}
           />
         </div>

         {/* Tabbed Interface Container */}
         <div className="mt-6">
           {/* Tab Navigation */}
           <div className="border-b border-slate-200 dark:border-slate-700">
             <nav className="-mb-px flex space-x-8">
               <button
                 onClick={() => setActiveTab('all')}
                 className={`whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm ${
                   activeTab === 'all'
                     ? 'border-green-500 text-green-600 dark:text-green-400 dark:border-green-400'
                     : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300 dark:hover:border-slate-500'
                 }`}
               >
                 {t('student_registration_all_applicants')}
               </button>
               <button
                 onClick={() => setActiveTab('pending')}
                 className={`whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm ${
                   activeTab === 'pending'
                     ? 'border-green-500 text-green-600 dark:text-green-400 dark:border-green-400'
                     : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300 dark:hover:border-slate-500'
                 }`}
               >
                 {t('student_registration_pending')}
               </button>
               <button
                 onClick={() => setActiveTab('approved')}
                 className={`whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm ${
                   activeTab === 'approved'
                     ? 'border-green-500 text-green-600 dark:text-green-400 dark:border-green-400'
                     : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300 dark:hover:border-slate-500'
                 }`}
               >
                 {t('student_registration_approved')}
               </button>
               <button
                 onClick={() => setActiveTab('rejected')}
                 className={`whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm ${
                   activeTab === 'rejected'
                     ? 'border-green-500 text-green-600 dark:text-green-400 dark:border-green-400'
                     : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300 dark:hover:border-slate-500'
                 }`}
               >
                 {t('student_registration_rejected')}
               </button>
             </nav>
           </div>




           </div>
         </div>



         {/* Tab Content */}
         <div className="mt-6">
           {/* All Registrations Table - shown when activeTab is 'all' */}
           {activeTab === 'all' && (
             <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
               <div className="flex justify-between items-center mb-4">
                 <h2 className="text-xl font-bold text-slate-800 dark:text-white">
                   {t('student_registration_all_applicants')}
                 </h2>
               </div>
               
               <div className="overflow-x-auto">
                 <table className="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                   <thead className="bg-slate-50 dark:bg-slate-700">
                     <tr>
                       <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                         {t('student_registration_student')}
                       </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                        {t('student_registration_simple_status')}
                      </th>
                       <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                         {t('student_registration_date')}
                       </th>
                       <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                         {t('student_registration_actions')}
                       </th>
                     </tr>
                   </thead>
                   <tbody className="bg-white dark:bg-slate-800/50 divide-y divide-slate-200 dark:divide-slate-700">
                     {registrations.map((registration) => (
                       <tr key={registration.id} className="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                         <td className="px-6 py-4">
                           <div className="flex items-center">
                             <div className="flex-shrink-0 h-10 w-10 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center">
                               <span className="text-indigo-800 dark:text-indigo-200 font-medium">
                                 {registration.name.charAt(0)}
                               </span>
                             </div>
                             <div className="ml-4">
                               <div className="text-sm font-medium text-slate-800 dark:text-white">{registration.name}</div>
                               <div className="text-sm text-slate-500 dark:text-slate-400">{registration.studentId}</div>
                               <div className="text-xs text-slate-400 dark:text-slate-500">{registration.email}</div>
                             </div>
                           </div>
                         </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <span className={`px-2.5 py-0.5 rounded-full text-xs font-semibold ${
                            registration.paymentStatus === 'completed' 
                              ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' 
                              : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300'
                          }`}>
                            {t(`student_registration_payment_${registration.paymentStatus === 'completed' ? 'completed' : 'pending'}`)}
                          </span>
                        </td>
                         <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                           {new Date(registration.registrationDate).toLocaleDateString('id-ID', {
                             year: 'numeric',
                             month: 'short',
                             day: 'numeric'
                           })}
                         </td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                          <button 
                            onClick={() => handleViewDetails(registration)}
                            className="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200 rounded-md text-sm font-medium transition-colors duration-200"
                          >
                            {t('student_registration_view')}
                          </button>
                        </td>
                       </tr>
                     ))}
                   </tbody>
                 </table>
               </div>
             </div>
            )}

            {/* Pending Registrations Table - shown when activeTab is 'pending' */}
            {activeTab === 'pending' && (
              <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                <div className="flex justify-between items-center mb-4">
                  <h2 className="text-xl font-bold text-slate-800 dark:text-white">
                    {t('student_registration_pending_registrations')}
                  </h2>
                </div>
                
                {pendingRegistrations.length === 0 ? (
                  <div className="text-center py-10">
                    <div className="mx-auto w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                      <Icon className="w-8 h-8 text-slate-400">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                      </Icon>
                    </div>
                    <h3 className="text-lg font-medium text-slate-800 dark:text-white mb-1">
                      {t('student_registration_no_pending_title')}
                    </h3>
                    <p className="text-slate-500 dark:text-slate-400">
                      {t('student_registration_no_pending_subtitle')}
                    </p>
                  </div>
                ) : (
                  <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                      <thead className="bg-slate-50 dark:bg-slate-700">
                        <tr>
                          <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                            {t('student_registration_student')}
                          </th>
                         <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                           {t('student_registration_simple_status')}
                         </th>
                          <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                            {t('student_registration_date')}
                          </th>
                          <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                            {t('student_registration_actions')}
                          </th>
                        </tr>
                      </thead>
                      <tbody className="bg-white dark:bg-slate-800/50 divide-y divide-slate-200 dark:divide-slate-700">
                        {pendingRegistrations.map((registration) => (
                          <tr key={registration.id} className="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                            <td className="px-6 py-4">
                              <div className="flex items-center">
                                <div className="flex-shrink-0 h-10 w-10 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center">
                                  <span className="text-indigo-800 dark:text-indigo-200 font-medium">
                                    {registration.name.charAt(0)}
                                  </span>
                                </div>
                                <div className="ml-4">
                                  <div className="text-sm font-medium text-slate-800 dark:text-white">{registration.name}</div>
                                  <div className="text-sm text-slate-500 dark:text-slate-400">{registration.studentId}</div>
                                  <div className="text-xs text-slate-400 dark:text-slate-500">{registration.email}</div>
                                </div>
                              </div>
                            </td>
                          <td className="px-6 py-4 whitespace-nowrap">
                            <span className={`px-2.5 py-0.5 rounded-full text-xs font-semibold ${
                              registration.paymentStatus === 'completed' 
                                ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' 
                                : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300'
                            }`}>
                              {t(`student_registration_payment_${registration.paymentStatus === 'completed' ? 'completed' : 'pending'}`)}
                            </span>
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                            {new Date(registration.registrationDate).toLocaleDateString('id-ID', {
                              year: 'numeric',
                              month: 'short',
                              day: 'numeric'
                            })}
                          </td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                           <button 
                             onClick={() => handleViewDetails(registration)}
                             className="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200 rounded-md text-sm font-medium transition-colors duration-200"
                           >
                             {t('student_registration_view')}
                           </button>
                          </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                )}
              </div>
            )}

            {/* Approved Registrations Table - shown when activeTab is 'approved' */}
            {activeTab === 'approved' && (
              <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                <div className="flex justify-between items-center mb-4">
                  <h2 className="text-xl font-bold text-slate-800 dark:text-white">
                    {t('student_registration_approved_registrations')}
                  </h2>
                </div>
                
                <div className="overflow-x-auto">
                  <table className="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead className="bg-slate-50 dark:bg-slate-700">
                      <tr>
                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                          {t('student_registration_student')}
                        </th>
                       <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                         {t('student_registration_simple_status')}
                       </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                          {t('student_registration_date')}
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                          {t('student_registration_actions')}
                        </th>
                      </tr>
                    </thead>
                    <tbody className="bg-white dark:bg-slate-800/50 divide-y divide-slate-200 dark:divide-slate-700">
                      {registrations.filter(r => r.paymentStatus === 'completed').map((registration) => (
                        <tr key={registration.id} className="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                          <td className="px-6 py-4">
                            <div className="flex items-center">
                              <div className="flex-shrink-0 h-10 w-10 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center">
                                <span className="text-indigo-800 dark:text-indigo-200 font-medium">
                                  {registration.name.charAt(0)}
                                </span>
                              </div>
                              <div className="ml-4">
                                <div className="text-sm font-medium text-slate-800 dark:text-white">{registration.name}</div>
                                <div className="text-sm text-slate-500 dark:text-slate-400">{registration.studentId}</div>
                                <div className="text-xs text-slate-400 dark:text-slate-500">{registration.email}</div>
                              </div>
                            </div>
                          </td>
                         <td className="px-6 py-4 whitespace-nowrap">
                           <span className={`px-2.5 py-0.5 rounded-full text-xs font-semibold ${
                             registration.paymentStatus === 'completed' 
                               ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' 
                               : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300'
                           }`}>
                             {t(`student_registration_payment_${registration.paymentStatus === 'completed' ? 'completed' : 'pending'}`)}
                           </span>
                         </td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                            {new Date(registration.registrationDate).toLocaleDateString('id-ID', {
                              year: 'numeric',
                              month: 'short',
                              day: 'numeric'
                            })}
                          </td>
                         <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                           <button 
                             onClick={() => handleViewDetails(registration)}
                             className="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200 rounded-md text-sm font-medium transition-colors duration-200"
                           >
                             {t('student_registration_view')}
                           </button>
                         </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            )}

            {/* Rejected Registrations Table - shown when activeTab is 'rejected' */}
            {activeTab === 'rejected' && (
              <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                <div className="flex justify-between items-center mb-4">
                  <h2 className="text-xl font-bold text-slate-800 dark:text-white">
                    {t('student_registration_rejected_registrations')}
                  </h2>
                </div>
                
                <div className="overflow-x-auto">
                  <table className="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead className="bg-slate-50 dark:bg-slate-700">
                      <tr>
                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                          {t('student_registration_student')}
                        </th>
                       <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                         {t('student_registration_simple_status')}
                       </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                          {t('student_registration_date')}
                        </th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                          {t('student_registration_actions')}
                        </th>
                      </tr>
                    </thead>
                    <tbody className="bg-white dark:bg-slate-800/50 divide-y divide-slate-200 dark:divide-slate-700">
                      {registrations.filter(r => r.paymentStatus === 'failed').map((registration) => (
                        <tr key={registration.id} className="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                          <td className="px-6 py-4">
                            <div className="flex items-center">
                              <div className="flex-shrink-0 h-10 w-10 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center">
                                <span className="text-indigo-800 dark:text-indigo-200 font-medium">
                                  {registration.name.charAt(0)}
                                </span>
                              </div>
                              <div className="ml-4">
                                <div className="text-sm font-medium text-slate-800 dark:text-white">{registration.name}</div>
                                <div className="text-sm text-slate-500 dark:text-slate-400">{registration.studentId}</div>
                                <div className="text-xs text-slate-400 dark:text-slate-500">{registration.email}</div>
                              </div>
                            </div>
                          </td>
                         <td className="px-6 py-4 whitespace-nowrap">
                           <span className={`px-2.5 py-0.5 rounded-full text-xs font-semibold ${
                             registration.paymentStatus === 'completed' 
                               ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' 
                               : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300'
                           }`}>
                             {t(`student_registration_payment_${registration.paymentStatus === 'completed' ? 'completed' : 'pending'}`)}
                           </span>
                         </td>
                          <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                            {new Date(registration.registrationDate).toLocaleDateString('id-ID', {
                              year: 'numeric',
                              month: 'short',
                              day: 'numeric'
                            })}
                          </td>
                         <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                           <button 
                             onClick={() => handleViewDetails(registration)}
                             className="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200 rounded-md text-sm font-medium transition-colors duration-200"
                           >
                             {t('student_registration_view')}
                           </button>
                         </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            )}

         {/* Detail Modal */}
        {showDetailModal && selectedStudent && (
          <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
              <div className="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <h3 className="text-lg font-bold text-slate-800 dark:text-white">
                  {t('student_registration_detail_title')}
                </h3>
                <button 
                  onClick={() => setShowDetailModal(false)}
                  className="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
                >
                  <Icon className="w-6 h-6">
                    <path d="M6 18L18 6M6 6l12 12"/>
                  </Icon>
                </button>
              </div>
              
               <div className="p-6">
                 <div className="mb-6 bg-slate-50 dark:bg-slate-700/50 p-5 rounded-xl">
                   <h4 className="text-xl font-bold text-slate-800 dark:text-white mb-4">
                     {t('student_registration_account_info')}
                   </h4>
                   <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_account_name')}
                       </label>
                       <p className="text-slate-800 dark:text-white">{selectedStudent.accountName}</p>
                     </div>
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_account_email')}
                       </label>
                       <p className="text-slate-800 dark:text-white">{selectedStudent.accountEmail}</p>
                     </div>
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_account_phone')}
                       </label>
                       <p className="text-slate-800 dark:text-white">{selectedStudent.accountPhone}</p>
                     </div>
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_account_created_date')}
                       </label>
                       <p className="text-slate-800 dark:text-white">
                         {new Date(selectedStudent.accountCreatedDate).toLocaleDateString('id-ID', {
                           day: 'numeric',
                           month: 'long',
                           year: 'numeric'
                         })}
                       </p>
                     </div>
                   </div>
                 </div>

                 <div className="mb-6 bg-slate-50 dark:bg-slate-700/50 p-5 rounded-xl">
                   <h4 className="text-xl font-bold text-slate-800 dark:text-white mb-4">
                     {t('student_registration_personal_info')}
                   </h4>
                   <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_full_name')}
                       </label>
                       <p className="text-slate-800 dark:text-white">{selectedStudent.fullName}</p>
                     </div>
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_nik')}
                       </label>
                       <p className="text-slate-800 dark:text-white">{selectedStudent.nik}</p>
                     </div>
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_gender')}
                       </label>
                       <p className="text-slate-800 dark:text-white">{selectedStudent.gender}</p>
                     </div>
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_birth_date')}
                       </label>
                       <p className="text-slate-800 dark:text-white">
                         {new Date(selectedStudent.birthDate).toLocaleDateString('id-ID', {
                           day: 'numeric',
                           month: 'long',
                           year: 'numeric'
                         })}
                       </p>
                     </div>
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_birth_place')}
                       </label>
                       <p className="text-slate-800 dark:text-white">{selectedStudent.birthPlace}</p>
                     </div>
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_religion')}
                       </label>
                       <p className="text-slate-800 dark:text-white">{selectedStudent.religion}</p>
                     </div>
                     <div className="md:col-span-2">
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_address')}
                       </label>
                       <p className="text-slate-800 dark:text-white">{selectedStudent.address}</p>
                     </div>
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_phone')}
                       </label>
                       <p className="text-slate-800 dark:text-white">{selectedStudent.phone}</p>
                     </div>
                   </div>
                 </div>

                 <div className="mb-6 bg-slate-50 dark:bg-slate-700/50 p-5 rounded-xl">
                   <h4 className="text-xl font-bold text-slate-800 dark:text-white mb-4">
                     {t('student_registration_parent_info')}
                   </h4>
                   <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_parent_name')}
                       </label>
                       <p className="text-slate-800 dark:text-white">{selectedStudent.parentName}</p>
                     </div>
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_parent_occupation')}
                       </label>
                       <p className="text-slate-800 dark:text-white">{selectedStudent.parentOccupation}</p>
                     </div>
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_parent_phone')}
                       </label>
                       <p className="text-slate-800 dark:text-white">{selectedStudent.parentPhone}</p>
                     </div>
                   </div>
                 </div>

                 <div className="mb-6 bg-slate-50 dark:bg-slate-700/50 p-5 rounded-xl">
                   <h4 className="text-xl font-bold text-slate-800 dark:text-white mb-4">
                     {t('student_registration_education_info')}
                   </h4>
                   <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_education_level')}
                       </label>
                       <p className="text-slate-800 dark:text-white">{selectedStudent.educationLevel}</p>
                     </div>
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_education_major')}
                       </label>
                       <p className="text-slate-800 dark:text-white">{selectedStudent.educationMajor}</p>
                     </div>
                     <div className="md:col-span-2">
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_school_name')}
                       </label>
                       <p className="text-slate-800 dark:text-white">{selectedStudent.educationName}</p>
                     </div>
                     <div className="md:col-span-2">
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_school_address')}
                       </label>
                       <p className="text-slate-800 dark:text-white">{selectedStudent.educationAddress}</p>
                     </div>
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_graduation_year')}
                       </label>
                       <p className="text-slate-800 dark:text-white">{selectedStudent.educationGraduationYear}</p>
                     </div>
                   </div>
                 </div>

                 <div className="mb-6 bg-slate-50 dark:bg-slate-700/50 p-5 rounded-xl">
                   <h4 className="text-xl font-bold text-slate-800 dark:text-white mb-4">
                     {t('student_registration_program_study')}
                   </h4>
                   <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_faculty')}
                       </label>
                       <p className="text-slate-800 dark:text-white">{selectedStudent.selectedFaculty}</p>
                     </div>
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_study_program')}
                       </label>
                       <p className="text-slate-800 dark:text-white">{selectedStudent.selectedProgram}</p>
                     </div>
                   </div>
                 </div>

                 <div className="mb-6 bg-slate-50 dark:bg-slate-700/50 p-5 rounded-xl">
                   <h4 className="text-xl font-bold text-slate-800 dark:text-white mb-4">
                     {t('student_registration_payment_status')}
                   </h4>
                   <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_payment_method')}
                       </label>
                       <p className="text-slate-800 dark:text-white">{selectedStudent.paymentMethod}</p>
                     </div>
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_payment_amount')}
                       </label>
                       <p className="text-slate-800 dark:text-white">{selectedStudent.paymentAmount}</p>
                     </div>
                     <div className="md:col-span-2">
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         {t('student_registration_payment_status')}
                       </label>
                       <span className={`px-2.5 py-0.5 rounded-full text-xs font-semibold ${
                         selectedStudent.paymentStatus === 'completed' 
                           ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' 
                           : selectedStudent.paymentStatus === 'failed' 
                             ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' 
                             : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300'
                       }`}>
                         {selectedStudent.paymentStatus === 'completed'
                           ? t('student_registration_payment_completed')
                           : selectedStudent.paymentStatus === 'failed'
                             ? t('student_registration_payment_failed')
                             : selectedStudent.paymentStatus === 'pending'
                               ? t('student_registration_payment_pending')
                               : selectedStudent.paymentStatus}
                       </span>
                     </div>
                   </div>
                 </div>

                 <div className="mb-8">
                   <h4 className="text-xl font-bold text-slate-800 dark:text-white mb-4">
                     {t('student_registration_attachments')}
                   </h4>
                   <div className="space-y-3">
                     <div className="flex items-center justify-between p-3 bg-slate-100 dark:bg-slate-600 rounded-lg">
                       <div className="flex items-center">
                         <div className="mr-3 text-blue-500">
                           <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                             <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                           </svg>
                         </div>
                         <div>
                           <p className="text-sm font-medium text-slate-800 dark:text-white">Kartu Tanda Penduduk (KTP)</p>
                           <p className="text-xs text-slate-500 dark:text-slate-400">PDF</p>
                         </div>
                       </div>
                       <a href="#" className="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Lihat</a>
                     </div>
                     <div className="flex items-center justify-between p-3 bg-slate-100 dark:bg-slate-600 rounded-lg">
                       <div className="flex items-center">
                         <div className="mr-3 text-blue-500">
                           <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                             <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                           </svg>
                         </div>
                         <div>
                           <p className="text-sm font-medium text-slate-800 dark:text-white">Ijazah Terakhir</p>
                           <p className="text-xs text-slate-500 dark:text-slate-400">PDF</p>
                         </div>
                       </div>
                       <a href="#" className="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Lihat</a>
                     </div>
                     <div className="flex items-center justify-between p-3 bg-slate-100 dark:bg-slate-600 rounded-lg">
                       <div className="flex items-center">
                         <div className="mr-3 text-blue-500">
                           <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                             <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                           </svg>
                         </div>
                         <div>
                           <p className="text-sm font-medium text-slate-800 dark:text-white">Nilai Raport</p>
                           <p className="text-xs text-slate-500 dark:text-slate-400">PDF</p>
                         </div>
                       </div>
                       <a href="#" className="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Lihat</a>
                     </div>
                     <div className="flex items-center justify-between p-3 bg-slate-100 dark:bg-slate-600 rounded-lg">
                       <div className="flex items-center">
                         <div className="mr-3 text-blue-500">
                           <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                             <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                           </svg>
                         </div>
                         <div>
                           <p className="text-sm font-medium text-slate-800 dark:text-white">Pas Foto</p>
                           <p className="text-xs text-slate-500 dark:text-slate-400">JPG</p>
                         </div>
                       </div>
                       <a href="#" className="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Lihat</a>
                     </div>
                   </div>
                 </div>

                <div className="flex justify-end space-x-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                  <button
                    type="button"
                    className="px-4 py-2 text-sm font-semibold text-slate-700 bg-slate-400 hover:bg-slate-500 rounded-lg transition-colors duration-200"
                    onClick={() => setShowDetailModal(false)}
                  >
                    {t('student_registration_close')}
                  </button>
                  <button
                    type="button"
                    className="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors duration-200"
                    onClick={() => handleReject(selectedStudent.id)}
                  >
                    {t('student_registration_reject')}
                  </button>
                  <button
                    type="button"
                    className="px-4 py-2 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors duration-200"
                    onClick={() => handleApprove(selectedStudent.id)}
                  >
                    {t('student_registration_approve')}
                  </button>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default StudentRegistrationPage;