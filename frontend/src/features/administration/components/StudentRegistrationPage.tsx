import React, { useState, useEffect } from 'react';
import { Icon } from '../../../ui/components/Icon';
import { useLanguage } from '../../../../contexts/LanguageContext';
import { managementAPI } from '../../../../services/apiService';

interface StudentRegistration {
  id: string;
  name: string;
  email: string;
  studentId: string;
  program: string;
  registrationDate: string;
  status: 'submitted' | 'under_review' | 'accepted' | 'rejected';
  // Registration form fields
  nik: string;
  fullName: string;
  gender: string;
  birthDate: string;
  birthPlace: string;
  religion: string;
  address: string;
  city: string;
  postalCode: string;
  citizenship: string;
  parentName: string;
  parentOccupation: string;
  parentPhone: string;
  educationLevel: string;
  educationMajor: string;
  educationName: string;
  educationAddress: string;
  educationGraduationYear: string;
  averageGrade: number;
  firstChoiceId: string;
  firstChoiceName: string;
  secondChoiceId: string | null;
  secondChoiceName: string | null;
  rejectionReason: string | null;
  reviewedBy: number | null;
  reviewerName: string | null;
  reviewedAt: string | null;
  submittedAt: string | null;
  documents: string[];
}

export const StudentRegistrationPage: React.FC = () => {
  const { t } = useLanguage();
  const [registrations, setRegistrations] = useState<StudentRegistration[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [activeTab, setActiveTab] = useState<'all' | 'submitted' | 'under_review' | 'accepted' | 'rejected'>('all');
  const [showDetailModal, setShowDetailModal] = useState(false);
  const [selectedStudent, setSelectedStudent] = useState<StudentRegistration | null>(null);
  const [showRejectModal, setShowRejectModal] = useState(false);
  const [rejectionReason, setRejectionReason] = useState('');

  // Fetch all registrations once on component mount
  useEffect(() => {
    fetchRegistrations();
  }, []);

  const fetchRegistrations = async () => {
    try {
      setLoading(true);
      setError(null);
      
      // Fetch all registrations without status filter
      const response = await managementAPI.getRegistrations({});
      
      // Handle paginated response structure
      // Response format: { success: true, data: { current_page, data: [...], total, ... } }
      const paginationData = response.data?.data;
      const dataArray = paginationData?.data || paginationData || response.data?.data?.data || [];
      
      // Ensure we have an array
      const registrationsArray = Array.isArray(dataArray) ? dataArray : [];
      
      // Transform backend data to frontend format
      const transformedData = registrationsArray.map((reg: any) => {
        // Log the raw data for debugging
        
        return {
          id: reg.id || '',
          name: reg.user_name || reg.name || reg.nik || 'Unknown',
          email: reg.user_email || '',
          studentId: reg.student_id || '',
          program: reg.first_choice?.name || reg.first_choice_name || 'Not specified',
          registrationDate: reg.created_at || reg.submitted_at || new Date().toISOString(),
          status: reg.status || 'submitted',
          nik: reg.nik || '',
          fullName: reg.user_name || reg.name || reg.nik || 'Unknown',
          gender: reg.gender === 'male' ? 'Laki-laki' : reg.gender === 'female' ? 'Perempuan' : reg.gender || '-',
          birthDate: reg.date_of_birth || '',
          birthPlace: reg.place_of_birth || '',
          religion: reg.religion || '',
          address: reg.address || '',
          city: reg.city || '',
          postalCode: reg.postal_code || '',
          citizenship: reg.citizenship || '',
          parentName: reg.parent_name || '',
          parentOccupation: reg.parent_job || '',
          parentPhone: reg.parent_phone || '',
          educationLevel: reg.school_type || '',
          educationMajor: reg.school_major || '',
          educationName: reg.school_name || '',
          educationAddress: reg.school_address || '',
          educationGraduationYear: reg.graduation_year_school || '',
          averageGrade: reg.average_grade || 0,
          firstChoiceId: reg.first_choice_id || '',
          firstChoiceName: reg.first_choice?.name || reg.first_choice_name || '',
          secondChoiceId: reg.second_choice_id || null,
          secondChoiceName: reg.second_choice_name || null,
          rejectionReason: reg.rejection_reason || null,
          reviewedBy: reg.reviewed_by || null,
          reviewerName: reg.reviewer_name || null,
          reviewedAt: reg.reviewed_at || null,
          submittedAt: reg.submitted_at || null,
          documents: reg.documents || [],
        };
      });

      setRegistrations(transformedData);
    } catch (err: any) {
      console.error('Error fetching registrations:', err);
      setError(err.response?.data?.message || err.message || 'Failed to load registrations');
    } finally {
      setLoading(false);
    }
  };

  const handleApprove = async (id: string) => {
    try {
      await managementAPI.reviewRegistration(id, 'accepted');
      setShowDetailModal(false);
      fetchRegistrations();
      alert('Registration accepted successfully!');
    } catch (err: any) {
      console.error('Error approving registration:', err);
      alert(err.response?.data?.message || 'Failed to approve registration');
    }
  };

  const handleReject = async () => {
    if (!selectedStudent || !rejectionReason.trim()) {
      alert('Please provide a rejection reason');
      return;
    }

    try {
      await managementAPI.reviewRegistration(selectedStudent.id, 'rejected', rejectionReason);
      setShowRejectModal(false);
      setShowDetailModal(false);
      setRejectionReason('');
      fetchRegistrations();
      alert('Registration rejected successfully!');
    } catch (err: any) {
      console.error('Error rejecting registration:', err);
      alert(err.response?.data?.message || 'Failed to reject registration');
    }
  };

  const handleViewDetails = async (student: StudentRegistration) => {
    try {
      const response = await managementAPI.getRegistrationById(student.id);
      const data = response.data.data;
      
      // Transform detailed data
      const detailedStudent: StudentRegistration = {
        ...student,
        ...data,
        gender: data.gender === 'male' ? 'Laki-laki' : data.gender === 'female' ? 'Perempuan' : data.gender,
      };
      
      setSelectedStudent(detailedStudent);
      setShowDetailModal(true);
    } catch (err: any) {
      console.error('Error fetching registration details:', err);
      alert('Failed to load registration details');
    }
  };

  const pendingRegistrations = registrations.filter(reg => reg.status === 'submitted' || reg.status === 'under_review');
  
  // Filter registrations based on active tab
  const filteredRegistrations = activeTab === 'all' 
    ? registrations 
    : registrations.filter(reg => reg.status === activeTab);

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

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto mb-4"></div>
          <p className="text-slate-600 dark:text-slate-400">Loading registrations...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center bg-red-50 dark:bg-red-900/20 p-8 rounded-xl">
          <Icon className="w-16 h-16 text-red-500 mx-auto mb-4">
            <path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </Icon>
          <h3 className="text-lg font-semibold text-red-800 dark:text-red-200 mb-2">Error Loading Data</h3>
          <p className="text-red-600 dark:text-red-400 mb-4">{error}</p>
          <button
            onClick={fetchRegistrations}
            className="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors"
          >
            Try Again
          </button>
        </div>
      </div>
    );
  }

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
         <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-6">
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
              value={registrations.filter(r => r.status === 'accepted').length.toString()}
             label={t('student_registration_approved')}
           />
           <StatCard 
             icon={<Icon className="w-8 h-8 text-red-500"><circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="2"/><path d="m15 9-6 6m0-6 6 6" stroke="currentColor" strokeWidth="2" strokeLinecap="round"/></Icon>} 
              value={registrations.filter(r => r.status === 'rejected').length.toString()}
             label={t('student_registration_rejected')}
           />
         </div>

         {/* Tabbed Interface Container */}
         <div className="mt-6">
           {/* Tab Navigation */}
           <div className="border-b border-slate-200 dark:border-slate-700">
             <nav className="-mb-px flex space-x-8 overflow-x-auto">
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
                 onClick={() => setActiveTab('submitted')}
                 className={`whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm ${
                   activeTab === 'submitted'
                     ? 'border-green-500 text-green-600 dark:text-green-400 dark:border-green-400'
                     : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300 dark:hover:border-slate-500'
                 }`}
               >
                 {t('student_registration_submitted')}
               </button>
               <button
                 onClick={() => setActiveTab('under_review')}
                 className={`whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm ${
                   activeTab === 'under_review'
                     ? 'border-green-500 text-green-600 dark:text-green-400 dark:border-green-400'
                     : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300 dark:hover:border-slate-500'
                 }`}
               >
                 {t('student_registration_under_review')}
               </button>
               <button
                 onClick={() => setActiveTab('accepted')}
                 className={`whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm ${
                   activeTab === 'accepted'
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

           {/* Tab Content */}
           <div className="mt-6">
             {/* Registrations Table */}
             <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
               <div className="flex justify-between items-center mb-4">
                 <h2 className="text-xl font-bold text-slate-800 dark:text-white">
                   {activeTab === 'all' ? t('student_registration_all_applicants') : 
                    activeTab === 'submitted' ? t('student_registration_submitted_registrations') :
                    activeTab === 'under_review' ? t('student_registration_under_review_registrations') :
                    activeTab === 'accepted' ? t('student_registration_approved_registrations') :
                    t('student_registration_rejected_registrations')}
                 </h2>
               </div>
               
               {filteredRegistrations.length === 0 ? (
                 <div className="text-center py-10">
                   <div className="mx-auto w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                     <Icon className="w-8 h-8 text-slate-400">
                       <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                     </Icon>
                   </div>
                   <h3 className="text-lg font-medium text-slate-800 dark:text-white mb-1">
                     No Registrations Found
                   </h3>
                   <p className="text-slate-500 dark:text-slate-400">
                     There are no registrations in this category
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
                           {t('student_registration_program')}
                         </th>
                         <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider">
                           {t('student_registration_status')}
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
                       {filteredRegistrations.map((registration) => (
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
                                 {registration.studentId && (
                                   <div className="text-sm text-slate-500 dark:text-slate-400">{registration.studentId}</div>
                                 )}
                                 <div className="text-xs text-slate-400 dark:text-slate-500">{registration.email}</div>
                               </div>
                             </div>
                           </td>
                           <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                             {registration.firstChoiceName || registration.program}
                           </td>
                           <td className="px-6 py-4 whitespace-nowrap">
                             <span className={`px-2.5 py-0.5 rounded-full text-xs font-semibold ${
                               registration.status === 'accepted'
                                 ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300'
                                 : registration.status === 'rejected'
                                   ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300'
                                   : registration.status === 'under_review'
                                     ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300'
                                     : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300'
                             }`}>
                               {registration.status === 'accepted' ? 'Accepted' :
                                registration.status === 'rejected' ? 'Rejected' :
                                registration.status === 'under_review' ? 'Under Review' :
                                'Submitted'}
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
           </div>
         </div>
      </div>

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
               {/* Personal Info */}
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
                       {selectedStudent.birthDate ? new Date(selectedStudent.birthDate).toLocaleDateString('id-ID', {
                         day: 'numeric',
                         month: 'long',
                         year: 'numeric'
                       }) : '-'}
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
                       City
                     </label>
                     <p className="text-slate-800 dark:text-white">{selectedStudent.city}</p>
                   </div>
                   <div>
                     <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                       Postal Code
                     </label>
                     <p className="text-slate-800 dark:text-white">{selectedStudent.postalCode}</p>
                   </div>
                 </div>
               </div>

               {/* Parent Info */}
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

               {/* Education Info */}
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
                   <div>
                     <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                       Average Grade
                     </label>
                     <p className="text-slate-800 dark:text-white">{selectedStudent.averageGrade}</p>
                   </div>
                 </div>
               </div>

               {/* Program Selection */}
               <div className="mb-6 bg-slate-50 dark:bg-slate-700/50 p-5 rounded-xl">
                 <h4 className="text-xl font-bold text-slate-800 dark:text-white mb-4">
                   {t('student_registration_program_study')}
                 </h4>
                 <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                   <div>
                     <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                       First Choice
                     </label>
                     <p className="text-slate-800 dark:text-white">{selectedStudent.firstChoiceName}</p>
                   </div>
                   <div>
                     <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                       Second Choice
                     </label>
                     <p className="text-slate-800 dark:text-white">{selectedStudent.secondChoiceName || '-'}</p>
                   </div>
                 </div>
               </div>

               {/* Status */}
               <div className="mb-6 bg-slate-50 dark:bg-slate-700/50 p-5 rounded-xl">
                 <h4 className="text-xl font-bold text-slate-800 dark:text-white mb-4">
                   Status
                 </h4>
                 <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                   <div>
                     <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                       Current Status
                     </label>
                     <span className={`px-2.5 py-0.5 rounded-full text-xs font-semibold ${
                       selectedStudent.status === 'accepted' 
                         ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' 
                         : selectedStudent.status === 'rejected' 
                           ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' 
                           : selectedStudent.status === 'under_review'
                             ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300'
                             : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300'
                     }`}>
                       {selectedStudent.status === 'accepted' ? 'Accepted' :
                        selectedStudent.status === 'rejected' ? 'Rejected' :
                        selectedStudent.status === 'under_review' ? 'Under Review' :
                        'Submitted'}
                     </span>
                   </div>
                   {selectedStudent.rejectionReason && (
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         Rejection Reason
                       </label>
                       <p className="text-red-600 dark:text-red-400">{selectedStudent.rejectionReason}</p>
                     </div>
                   )}
                   {selectedStudent.reviewedAt && (
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         Reviewed At
                       </label>
                       <p className="text-slate-800 dark:text-white">
                         {new Date(selectedStudent.reviewedAt).toLocaleDateString('id-ID', {
                           day: 'numeric',
                           month: 'long',
                           year: 'numeric'
                         })}
                       </p>
                     </div>
                   )}
                   {selectedStudent.reviewerName && (
                     <div>
                       <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                         Reviewed By
                       </label>
                       <p className="text-slate-800 dark:text-white">{selectedStudent.reviewerName}</p>
                     </div>
                   )}
                 </div>
               </div>

               {/* Documents */}
               {selectedStudent.documents && selectedStudent.documents.length > 0 && (
                 <div className="mb-8">
                   <h4 className="text-xl font-bold text-slate-800 dark:text-white mb-4">
                     {t('student_registration_attachments')}
                   </h4>
                   <div className="space-y-3">
                     {selectedStudent.documents.map((doc, index) => (
                       <div key={index} className="flex items-center justify-between p-3 bg-slate-100 dark:bg-slate-600 rounded-lg">
                         <div className="flex items-center">
                           <div className="mr-3 text-blue-500">
                             <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                               <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                             </svg>
                           </div>
                           <div>
                             <p className="text-sm font-medium text-slate-800 dark:text-white">{doc}</p>
                           </div>
                         </div>
                       </div>
                     ))}
                   </div>
                 </div>
               )}

              <div className="flex justify-end space-x-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                <button
                  type="button"
                  className="px-4 py-2 text-sm font-semibold text-slate-700 bg-slate-400 hover:bg-slate-500 rounded-lg transition-colors duration-200"
                  onClick={() => setShowDetailModal(false)}
                >
                  {t('student_registration_close')}
                </button>
                {selectedStudent.status === 'submitted' || selectedStudent.status === 'under_review' ? (
                  <>
                    <button
                      type="button"
                      className="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors duration-200"
                      onClick={() => {
                        setSelectedStudent(selectedStudent);
                        setShowRejectModal(true);
                      }}
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
                  </>
                ) : null}
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Reject Confirmation Modal */}
      {showRejectModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-xl max-w-md w-full p-6">
            <h3 className="text-lg font-bold text-slate-800 dark:text-white mb-4">
              Reject Registration
            </h3>
            <p className="text-slate-600 dark:text-slate-400 mb-4">
              Please provide a reason for rejecting this registration:
            </p>
            <textarea
              value={rejectionReason}
              onChange={(e) => setRejectionReason(e.target.value)}
              rows={4}
              className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-slate-700 dark:text-white"
              placeholder="Enter rejection reason..."
            />
            <div className="flex justify-end space-x-3 mt-4">
              <button
                onClick={() => {
                  setShowRejectModal(false);
                  setRejectionReason('');
                }}
                className="px-4 py-2 text-sm font-semibold text-slate-700 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 rounded-lg transition-colors"
              >
                Cancel
              </button>
              <button
                onClick={handleReject}
                className="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors"
              >
                Confirm Rejection
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default StudentRegistrationPage;
