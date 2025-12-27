import React, { useState } from 'react';
import { useLanguage } from '../../../../contexts/LanguageContext';

interface RegistrationFormData {
  personalInfo: {
    nisn: string;
    nik: string;
    birthDate: string;
    birthPlace: string;
    gender: string;
    religion: string;
    address: string;
    city: string;
    postalCode: string;
    nationality: string;
    parentName: string;
    parentPhone: string;
    parentOccupation: string;
  };
  educationInfo: {
    schoolName: string;
    schoolAddress: string;
    graduationYear: string;
    schoolType: string;
    major: string;
    averageGrade: string;
  };
  preferences: {
    firstChoice: string;
    secondChoice: string;
  };
  documents: {
    attachments: string[]; // Changed to array to handle multiple files
  };
}

export const RegistrasiPage: React.FC = () => {
  const { t } = useLanguage();
  const [formData, setFormData] = useState<RegistrationFormData>({
    personalInfo: {
      nisn: '',
      nik: '',
      birthDate: '',
      birthPlace: '',
      gender: '',
      religion: '',
      address: '',
      city: '',
      postalCode: '',
      nationality: 'Indonesia',
      parentName: '',
      parentPhone: '',
      parentOccupation: '',
    },
    educationInfo: {
      schoolName: '',
      schoolAddress: '',
      graduationYear: '',
      schoolType: '',
      major: '',
      averageGrade: '',
    },
    preferences: {
      firstChoice: '',
      secondChoice: '',
    },
    documents: {
      attachments: [],
    },
  });

  const handleChange = (section: keyof RegistrationFormData, field: string, value: any) => {
    setFormData(prev => ({
      ...prev,
      [section]: {
        ...prev[section],
        [field]: value
      }
    }));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    console.log('Registration form submitted:', formData);
    alert(t('form_submit_success'));
  };

   return (
     <div className="space-y-8">
       <div className="max-w-6xl mx-auto">
         <h1 className="text-3xl font-bold text-slate-800 dark:text-white">
           {t('student_registration_title')}
         </h1>
         <p className="text-slate-500 dark:text-slate-400 mt-1">
           {t('student_registration_subtitle')}
         </p>
        
        <form onSubmit={handleSubmit} className="space-y-8">
           {/* Personal Information */}
           <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
             <h2 className="text-xl font-bold text-slate-800 dark:text-white mb-4">
               {t('student_registration_personal_info')}
             </h2>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
               <div>
                 <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                   {t('student_registration_nisn')}
                 </label>
                 <input
                   type="text"
                   value={formData.personalInfo.nisn}
                   onChange={(e) => handleChange('personalInfo', 'nisn', e.target.value)}
                   placeholder={t('student_registration_nisn')}
                   className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white"
                 />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_nik')}
                </label>
                 <input
                   type="text"
                   value={formData.personalInfo.nik}
                   onChange={(e) => handleChange('personalInfo', 'nik', e.target.value)}
                   placeholder={t('student_registration_nik')}
                   className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white"
                 />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_birth_date')}
                </label>
                 <input
                   type="date"
                   value={formData.personalInfo.birthDate}
                   onChange={(e) => handleChange('personalInfo', 'birthDate', e.target.value)}
                   className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white"
                 />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_birth_place')}
                </label>
                 <input
                   type="text"
                   value={formData.personalInfo.birthPlace}
                   onChange={(e) => handleChange('personalInfo', 'birthPlace', e.target.value)}
                   placeholder={t('student_registration_birth_place')}
                   className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white"
                 />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_gender')}
                </label>
                 <select
                   value={formData.personalInfo.gender}
                   onChange={(e) => handleChange('personalInfo', 'gender', e.target.value)}
                   className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white"
                 >
                  <option value="">{t('student_registration_select_gender')}</option>
                  <option value="Laki-laki">{t('student_registration_male')}</option>
                  <option value="Perempuan">{t('student_registration_female')}</option>
                </select>
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_religion')}
                </label>
                 <select
                   value={formData.personalInfo.religion}
                   onChange={(e) => handleChange('personalInfo', 'religion', e.target.value)}
                   className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white"
                 >
                  <option value="">{t('student_registration_select_religion')}</option>
                  <option value="Islam">{t('religion_islam')}</option>
                  <option value="Kristen">{t('religion_kristen')}</option>
                  <option value="Katolik">{t('religion_katolik')}</option>
                  <option value="Hindu">{t('religion_hindu')}</option>
                  <option value="Buddha">{t('religion_buddha')}</option>
                  <option value="Konghucu">{t('religion_konghucu')}</option>
                </select>
              </div>
              
              <div className="md:col-span-2">
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_address')}
                </label>
                 <textarea
                   value={formData.personalInfo.address}
                   onChange={(e) => handleChange('personalInfo', 'address', e.target.value)}
                   placeholder={t('student_registration_address')}
                   className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white"
                   rows={3}
                 />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_city')}
                </label>
                 <input
                   type="text"
                   value={formData.personalInfo.city}
                   onChange={(e) => handleChange('personalInfo', 'city', e.target.value)}
                   placeholder={t('student_registration_city')}
                   className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white"
                 />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_postal_code')}
                </label>
                 <input
                   type="text"
                   value={formData.personalInfo.postalCode}
                   onChange={(e) => handleChange('personalInfo', 'postalCode', e.target.value)}
                   placeholder={t('student_registration_postal_code')}
                   className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white"
                 />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_nationality')}
                </label>
                 <input
                   type="text"
                   value={formData.personalInfo.nationality}
                   onChange={(e) => handleChange('personalInfo', 'nationality', e.target.value)}
                   placeholder={t('student_registration_nationality')}
                   className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white"
                 />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_parent_name')}
                </label>
                 <input
                   type="text"
                   value={formData.personalInfo.parentName}
                   onChange={(e) => handleChange('personalInfo', 'parentName', e.target.value)}
                   placeholder={t('student_registration_parent_name')}
                   className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white"
                 />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_parent_phone')}
                </label>
                 <input
                   type="text"
                   value={formData.personalInfo.parentPhone}
                   onChange={(e) => handleChange('personalInfo', 'parentPhone', e.target.value)}
                   placeholder={t('student_registration_parent_phone')}
                   className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white"
                 />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_parent_occupation')}
                </label>
                 <input
                   type="text"
                   value={formData.personalInfo.parentOccupation}
                   onChange={(e) => handleChange('personalInfo', 'parentOccupation', e.target.value)}
                   placeholder={t('student_registration_parent_occupation')}
                   className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white"
                 />
              </div>
            </div>
          </div>
          
           {/* Education Information */}
           <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
             <h2 className="text-xl font-bold text-slate-800 dark:text-white mb-4">
               {t('student_registration_education_info')}
             </h2>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_school_name')}
                </label>
                 <input
                   type="text"
                   value={formData.educationInfo.schoolName}
                   onChange={(e) => handleChange('educationInfo', 'schoolName', e.target.value)}
                   placeholder={t('student_registration_school_name')}
                   className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white"
                 />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_school_address')}
                </label>
                 <input
                   type="text"
                   value={formData.educationInfo.schoolAddress}
                   onChange={(e) => handleChange('educationInfo', 'schoolAddress', e.target.value)}
                   placeholder={t('student_registration_school_address')}
                   className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white"
                 />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_graduation_year')}
                </label>
                 <input
                   type="text"
                   value={formData.educationInfo.graduationYear}
                   onChange={(e) => handleChange('educationInfo', 'graduationYear', e.target.value)}
                   placeholder={t('student_registration_graduation_year')}
                   className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white"
                 />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_school_type')}
                </label>
                 <select
                   value={formData.educationInfo.schoolType}
                   onChange={(e) => handleChange('educationInfo', 'schoolType', e.target.value)}
                   className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white"
                 >
                  <option value="">{t('student_registration_select_school_type')}</option>
                  <option value="SMA">{t('school_type_sma')}</option>
                  <option value="SMK">{t('school_type_smk')}</option>
                  <option value="MA">{t('school_type_ma')}</option>
                  <option value="Paket C">{t('school_type_paket_c')}</option>
                </select>
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_major')}
                </label>
                 <input
                   type="text"
                   value={formData.educationInfo.major}
                   onChange={(e) => handleChange('educationInfo', 'major', e.target.value)}
                   placeholder={t('student_registration_major')}
                   className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white"
                 />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_average_grade')}
                </label>
                 <input
                   type="text"
                   value={formData.educationInfo.averageGrade}
                   onChange={(e) => handleChange('educationInfo', 'averageGrade', e.target.value)}
                   placeholder={t('student_registration_average_grade')}
                   className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white"
                 />
              </div>
            </div>
          </div>
          
           {/* Preferences */}
           <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
             <h2 className="text-xl font-bold text-slate-800 dark:text-white mb-4">
               {t('student_registration_preferences')}
             </h2>
            
<div className="grid grid-cols-1 md:grid-cols-2 gap-4">
               <div>
                 <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                   {t('student_registration_first_choice')}
                 </label>
 <select
                     value={formData.preferences.firstChoice}
                     onChange={(e) => handleChange('preferences', 'firstChoice', e.target.value)}
                     className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white"
                   >
                    <option value="">{t('student_registration_select_first_choice')}</option>
                    <option value="Fakultas Ushuluddin & Dakwah">{t('faculty_ushuluddin_dakwah')}</option>
                    <option value="Fakultas Syariah & Hukum">{t('faculty_syariah_hukum')}</option>
                    <option value="Fakultas Ekonomi & Manajemen Syariah">{t('faculty_ekonomi_syariah')}</option>
                    <option value="Fakultas Tarbiyah & Pendidikan Islam">{t('faculty_tarbiyah_islam')}</option>
                    <option value="Fakultas Adab, Humaniora & Bahasa">{t('faculty_adab_humaniora')}</option>
                    <option value="Fakultas Sains & Inovasi Islami">{t('faculty_sains_inovasi')}</option>
                    <option value="Fakultas Psikologi & Sosial">{t('faculty_psikologi_sosial')}</option>
                    <option value="Sekolah Pascasarjana">{t('faculty_pascasarjana')}</option>
                  </select>
               </div>
               
               <div>
                 <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                   {t('student_registration_second_choice')}
                 </label>
 <select
                     value={formData.preferences.secondChoice}
                     onChange={(e) => handleChange('preferences', 'secondChoice', e.target.value)}
                     className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white"
                   >
                    <option value="">{t('student_registration_select_second_choice')}</option>
                    <option value="Fakultas Ushuluddin & Dakwah">{t('faculty_ushuluddin_dakwah')}</option>
                    <option value="Fakultas Syariah & Hukum">{t('faculty_syariah_hukum')}</option>
                    <option value="Fakultas Ekonomi & Manajemen Syariah">{t('faculty_ekonomi_syariah')}</option>
                    <option value="Fakultas Tarbiyah & Pendidikan Islam">{t('faculty_tarbiyah_islam')}</option>
                    <option value="Fakultas Adab, Humaniora & Bahasa">{t('faculty_adab_humaniora')}</option>
                    <option value="Fakultas Sains & Inovasi Islami">{t('faculty_sains_inovasi')}</option>
                    <option value="Fakultas Psikologi & Sosial">{t('faculty_psikologi_sosial')}</option>
                    <option value="Sekolah Pascasarjana">{t('faculty_pascasarjana')}</option>
                  </select>
               </div>
             </div>
          </div>
          
           {/* Document Uploads */}
           <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
             <h2 className="text-xl font-bold text-slate-800 dark:text-white mb-4">
               {t('student_registration_documents')}
             </h2>
            
            <div className="grid grid-cols-1 gap-4">
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_attachments')}
                </label>
                <p className="text-xs text-slate-500 dark:text-slate-400 mb-2">
                  {t('student_registration_documents_hint')}
                </p>
                 <div className="flex items-center justify-center w-full">
                   <label htmlFor="dropzone-file" className="flex flex-col items-center justify-center w-full h-64 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl cursor-pointer bg-slate-50 dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 transition-colors">
                     <div className="flex flex-col items-center justify-center pt-5 pb-6">
                       <svg className="w-10 h-10 mb-3 text-slate-500 dark:text-slate-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                         <path stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                       </svg>
                       <p className="mb-2 text-sm text-slate-500 dark:text-slate-400">
                         <span className="font-semibold">{t('student_registration_click_to_upload')}</span> {t('student_registration_or_drag_drop')}
                       </p>
                       <p className="text-xs text-slate-500 dark:text-slate-400">
                         {t('student_registration_attachments_help')}
                       </p>
                     </div>
                     <input 
                       id="dropzone-file" 
                       type="file" 
                       multiple
                       onChange={(e) => {
                         if (e.target.files) {
                           const files = Array.from(e.target.files);
                           const fileUrls = files.map(file => URL.createObjectURL(file));
                           handleChange('documents', 'attachments', fileUrls);
                         }
                       }}
                       className="hidden" 
                     />
                   </label>
                 </div>
              </div>
            </div>
          </div>
          
           <div className="flex justify-end pt-4">
             <button
               type="submit"
               className="px-6 py-3 bg-brand-emerald-600 hover:bg-brand-emerald-700 text-white font-semibold rounded-lg shadow-sm transition-colors"
             >
               {t('student_registration_submit')}
             </button>
           </div>
        </form>
      </div>
    </div>
  );
};