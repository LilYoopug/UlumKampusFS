import React, { useState, useEffect } from 'react';
import { useLanguage } from '../../../../contexts/LanguageContext';
import { registrationAPI, majorAPI } from '../../../../services/apiService';
import { Major } from '../../../../types';

interface RegistrationFormData {
  // Informasi Pribadi
  nisn: string;
  nik: string;
  date_of_birth: string;
  place_of_birth: string;
  gender: 'male' | 'female' | '';
  religion: string;
  address: string;
  city: string;
  postal_code: string;
  citizenship: string;
  parent_name: string;
  parent_phone: string;
  parent_job: string;
  
  // Informasi Pendidikan
  school_name: string;
  school_address: string;
  graduation_year_school: string;
  school_type: 'SMA' | 'SMK' | 'MA' | 'Lainnya' | '';
  school_major: string;
  average_grade: string;
  
  // Preferensi
  first_choice_id: string;
  second_choice_id: string;
  
  // Documents
  documents: string[];
}

const DRAFT_STORAGE_KEY = 'registration_draft';

export const RegistrasiPage: React.FC = () => {
  const { t } = useLanguage();
  const [formData, setFormData] = useState<RegistrationFormData>({
    // Informasi Pribadi
    nisn: '',
    nik: '',
    date_of_birth: '',
    place_of_birth: '',
    gender: '',
    religion: '',
    address: '',
    city: '',
    postal_code: '',
    citizenship: 'Indonesia',
    parent_name: '',
    parent_phone: '',
    parent_job: '',
    
    // Informasi Pendidikan
    school_name: '',
    school_address: '',
    graduation_year_school: '',
    school_type: '',
    school_major: '',
    average_grade: '',
    
    // Preferensi
    first_choice_id: '',
    second_choice_id: '',
    
    // Documents
    documents: [],
  });
  
  const [majors, setMajors] = useState<Major[]>([]);
  const [loading, setLoading] = useState(false);
  const [loadingInitial, setLoadingInitial] = useState(true);
  const [saving, setSaving] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);
  const [existingRegistration, setExistingRegistration] = useState<any>(null);
  const [fileInputs, setFileInputs] = useState<File[]>([]);

  // Load majors
  useEffect(() => {
    const loadMajors = async () => {
      try {
        const response = await majorAPI.getAll();
        // Handle different response structures
        // The API returns paginated response: { success, message, data: { data: [...], ... } }
        let majorsData: Major[] = [];
        
        
        const responseData = response.data as any;
        
        // Check if response.data is the array directly
        if (Array.isArray(responseData)) {
          majorsData = responseData as Major[];
        }
        // Check if response.data.data is the array (standard API response)
        else if (responseData?.data) {
          if (Array.isArray(responseData.data)) {
            majorsData = responseData.data as Major[];
          }
          // Check if it's paginated: { data: { data: [...] } }
          else if (responseData.data.data && Array.isArray(responseData.data.data)) {
            majorsData = responseData.data.data as Major[];
          }
        }
        
        setMajors(majorsData);
      } catch (error) {
        console.error('Error loading majors:', error);
        setMajors([]);
      }
    };
    loadMajors();
  }, []);

  // Load draft from localStorage only
  useEffect(() => {
    const loadData = () => {
      try {
        setLoadingInitial(true);
        
        // Load draft from localStorage
        const savedDraft = localStorage.getItem(DRAFT_STORAGE_KEY);
        if (savedDraft) {
          const draftData = JSON.parse(savedDraft);
          setFormData(draftData);
        }
      } catch (error) {
        console.error('Error loading draft:', error);
      } finally {
        setLoadingInitial(false);
      }
    };
    loadData();
  }, []);

  const handleChange = (field: keyof RegistrationFormData, value: string) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files) {
      const files = Array.from(e.target.files);
      setFileInputs(prev => [...prev, ...files]);
    }
  };

  const removeFile = (index: number) => {
    setFileInputs(prev => prev.filter((_, i) => i !== index));
  };


  const showMessage = (type: 'success' | 'error', text: string) => {
    setMessage({ type, text });
    setTimeout(() => setMessage(null), 5000);
  };

  const validateForm = (): boolean => {
    // Required fields validation
    const missingFields: string[] = [];
    
    if (!formData.nisn) missingFields.push('NISN');
    if (!formData.nik) missingFields.push('NIK');
    if (!formData.date_of_birth) missingFields.push('Tanggal Lahir');
    if (!formData.place_of_birth) missingFields.push('Tempat Lahir');
    if (!formData.gender) missingFields.push('Jenis Kelamin');
    if (!formData.address) missingFields.push('Alamat');
    if (!formData.city) missingFields.push('Kota');
    if (!formData.parent_name) missingFields.push('Nama Orang Tua');
    if (!formData.parent_phone) missingFields.push('Telepon Orang Tua');
    if (!formData.school_name) missingFields.push('Nama Sekolah');
    if (!formData.school_address) missingFields.push('Alamat Sekolah');
    if (!formData.graduation_year_school) missingFields.push('Tahun Lulus');
    if (!formData.school_type) missingFields.push('Jenis Sekolah');
    if (!formData.school_major) missingFields.push('Jurusan');
    if (!formData.average_grade) missingFields.push('Rata-rata Nilai');
    if (!formData.first_choice_id) missingFields.push('Pilihan Pertama');
    
    if (missingFields.length > 0) {
      showMessage('error', `Please fill in all required fields: ${missingFields.join(', ')}`);
      return false;
    }
    return true;
  };

  const handleSaveDraft = (e: React.FormEvent) => {
    e.preventDefault();
    
    try {
      setSaving(true);
      
      // Save draft to localStorage
      localStorage.setItem(DRAFT_STORAGE_KEY, JSON.stringify(formData));
      
      showMessage('success', 'Draft saved successfully to local storage');
    } catch (error) {
      console.error('Error saving draft:', error);
      showMessage('error', 'Failed to save draft to local storage');
    } finally {
      setSaving(false);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!validateForm()) return;
    
    try {
      setSubmitting(true);
      
      // Convert string values to proper types for backend
      const submissionData = {
        ...formData,
        graduation_year_school: parseInt(formData.graduation_year_school, 10),
        average_grade: parseFloat(formData.average_grade),
        documents: formData.documents,
      };
      
      
      // Submit registration to backend
      const response = await registrationAPI.saveRegistration(submissionData);
      
      setExistingRegistration(response.data.data || response.data);
      
      // Clear draft from localStorage after successful submission
      localStorage.removeItem(DRAFT_STORAGE_KEY);
      
      showMessage('success', 'Registration submitted successfully! Please wait for review.');
    } catch (error: any) {
      console.error('Error submitting registration:', error);
      console.error('Error response:', error.response?.data);
      
      // Show detailed error message
      let errorMessage = 'Failed to submit registration';
      if (error.response?.data?.message) {
        errorMessage = error.response.data.message;
      }
      if (error.response?.data?.errors) {
        const errors = error.response.data.errors;
        const errorMessages = Object.values(errors).flat();
        if (errorMessages.length > 0) {
          errorMessage = errorMessages.join(', ');
        }
      }
      
      showMessage('error', errorMessage);
    } finally {
      setSubmitting(false);
    }
  };

  if (loadingInitial) {
    return (
      <div className="flex justify-center items-center min-h-screen">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-emerald-500"></div>
      </div>
    );
  }

  // Check if registration can be edited
  const canEdit = !existingRegistration || 
                  existingRegistration.status === 'draft' || 
                  existingRegistration.status === 'rejected';

  return (
    <div className="space-y-8">
      <div className="max-w-6xl mx-auto">
        <h1 className="text-3xl font-bold text-slate-800 dark:text-white">
          {t('student_registration_title')}
        </h1>
        <p className="text-slate-500 dark:text-slate-400 mt-1">
          {t('student_registration_subtitle')}
        </p>

        {/* Status Banner */}
        {existingRegistration && existingRegistration.status !== 'draft' && (
          <div className={`mt-4 p-4 rounded-lg ${
            existingRegistration.status === 'submitted' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-200' :
            existingRegistration.status === 'under_review' ? 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-200' :
            existingRegistration.status === 'accepted' ? 'bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200' :
            'bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-200'
          }`}>
            <p className="font-semibold">
              Status: {existingRegistration.status.replace('_', ' ').toUpperCase()}
            </p>
            {existingRegistration.rejection_reason && (
              <p className="mt-1 text-sm">Reason: {existingRegistration.rejection_reason}</p>
            )}
          </div>
        )}

        {/* Message Banner */}
        {message && (
          <div className={`mt-4 p-4 rounded-lg ${
            message.type === 'success' 
              ? 'bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200' 
              : 'bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-200'
          }`}>
            <p>{message.text}</p>
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-8">
          {/* Personal Information */}
          <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
            <h2 className="text-xl font-bold text-slate-800 dark:text-white mb-4">
              {t('student_registration_personal_info')}
            </h2>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                  {t('student_registration_nisn')} *
                </label>
                <input
                  type="text"
                  value={formData.nisn}
                  onChange={(e) => handleChange('nisn', e.target.value)}
                  placeholder={t('student_registration_nisn')}
                  disabled={!canEdit}
                  className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_nik')} *
                </label>
                <input
                  type="text"
                  value={formData.nik}
                  onChange={(e) => handleChange('nik', e.target.value)}
                  placeholder={t('student_registration_nik')}
                  disabled={!canEdit}
                  className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_birth_date')} *
                </label>
                <input
                  type="date"
                  value={formData.date_of_birth}
                  onChange={(e) => handleChange('date_of_birth', e.target.value)}
                  disabled={!canEdit}
                  className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_birth_place')} *
                </label>
                <input
                  type="text"
                  value={formData.place_of_birth}
                  onChange={(e) => handleChange('place_of_birth', e.target.value)}
                  placeholder={t('student_registration_birth_place')}
                  disabled={!canEdit}
                  className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_gender')} *
                </label>
                <select
                  value={formData.gender}
                  onChange={(e) => handleChange('gender', e.target.value)}
                  disabled={!canEdit}
                  className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <option value="">{t('student_registration_select_gender')}</option>
                  <option value="male">{t('student_registration_male')}</option>
                  <option value="female">{t('student_registration_female')}</option>
                </select>
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_religion')}
                </label>
                <input
                  type="text"
                  value={formData.religion}
                  onChange={(e) => handleChange('religion', e.target.value)}
                  placeholder="Islam, Kristen, dll."
                  disabled={!canEdit}
                  className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                />
              </div>
              
              <div className="md:col-span-2">
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_address')} *
                </label>
                <textarea
                  value={formData.address}
                  onChange={(e) => handleChange('address', e.target.value)}
                  placeholder={t('student_registration_address')}
                  disabled={!canEdit}
                  className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                  rows={3}
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_city')} *
                </label>
                <input
                  type="text"
                  value={formData.city}
                  onChange={(e) => handleChange('city', e.target.value)}
                  placeholder={t('student_registration_city')}
                  disabled={!canEdit}
                  className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_postal_code')}
                </label>
                <input
                  type="text"
                  value={formData.postal_code}
                  onChange={(e) => handleChange('postal_code', e.target.value)}
                  placeholder={t('student_registration_postal_code')}
                  disabled={!canEdit}
                  className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_nationality')}
                </label>
                <input
                  type="text"
                  value={formData.citizenship}
                  onChange={(e) => handleChange('citizenship', e.target.value)}
                  placeholder={t('student_registration_nationality')}
                  disabled={!canEdit}
                  className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_parent_name')} *
                </label>
                <input
                  type="text"
                  value={formData.parent_name}
                  onChange={(e) => handleChange('parent_name', e.target.value)}
                  placeholder={t('student_registration_parent_name')}
                  disabled={!canEdit}
                  className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_parent_phone')} *
                </label>
                <input
                  type="text"
                  value={formData.parent_phone}
                  onChange={(e) => handleChange('parent_phone', e.target.value)}
                  placeholder={t('student_registration_parent_phone')}
                  disabled={!canEdit}
                  className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_parent_occupation')}
                </label>
                <input
                  type="text"
                  value={formData.parent_job}
                  onChange={(e) => handleChange('parent_job', e.target.value)}
                  placeholder={t('student_registration_parent_occupation')}
                  disabled={!canEdit}
                  className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
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
                  {t('student_registration_school_name')} *
                </label>
                <input
                  type="text"
                  value={formData.school_name}
                  onChange={(e) => handleChange('school_name', e.target.value)}
                  placeholder={t('student_registration_school_name')}
                  disabled={!canEdit}
                  className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_school_address')} *
                </label>
                <input
                  type="text"
                  value={formData.school_address}
                  onChange={(e) => handleChange('school_address', e.target.value)}
                  placeholder={t('student_registration_school_address')}
                  disabled={!canEdit}
                  className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_graduation_year')} *
                </label>
                <input
                  type="number"
                  value={formData.graduation_year_school}
                  onChange={(e) => handleChange('graduation_year_school', e.target.value)}
                  placeholder="2024"
                  disabled={!canEdit}
                  className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_school_type')} *
                </label>
                <select
                  value={formData.school_type}
                  onChange={(e) => handleChange('school_type', e.target.value)}
                  disabled={!canEdit}
                  className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <option value="">{t('student_registration_select_school_type')}</option>
                  <option value="SMA">SMA</option>
                  <option value="SMK">SMK</option>
                  <option value="MA">MA</option>
                  <option value="Lainnya">Lainnya</option>
                </select>
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_major')} *
                </label>
                <input
                  type="text"
                  value={formData.school_major}
                  onChange={(e) => handleChange('school_major', e.target.value)}
                  placeholder={t('student_registration_major')}
                  disabled={!canEdit}
                  className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_average_grade')} *
                </label>
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  value={formData.average_grade}
                  onChange={(e) => handleChange('average_grade', e.target.value)}
                  placeholder="85.50"
                  disabled={!canEdit}
                  className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
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
                  {t('student_registration_first_choice')} *
                </label>
                <select
                  value={formData.first_choice_id}
                  onChange={(e) => handleChange('first_choice_id', e.target.value)}
                  disabled={!canEdit}
                  className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <option value="">{t('student_registration_select_first_choice')}</option>
                  {majors.map((major) => (
                    <option key={major.code} value={major.code}>
                      {major.name}
                    </option>
                  ))}
                </select>
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                  {t('student_registration_second_choice')}
                </label>
                <select
                  value={formData.second_choice_id}
                  onChange={(e) => handleChange('second_choice_id', e.target.value)}
                  disabled={!canEdit}
                  className="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 focus:border-brand-emerald-500 dark:bg-slate-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <option value="">{t('student_registration_select_second_choice')}</option>
                  {majors.map((major) => (
                    <option key={major.code} value={major.code}>
                      {major.name}
                    </option>
                  ))}
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
                  Format: PDF, JPG, JPEG, PNG (Max 5MB per file)
                </p>
                <div className="flex items-center justify-center w-full">
                  <label htmlFor="dropzone-file" className={`flex flex-col items-center justify-center w-full h-64 border-2 border-dashed rounded-xl cursor-pointer transition-colors ${
                    !canEdit
                      ? 'border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 cursor-not-allowed'
                      : 'border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600'
                  }`}>
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
                      accept=".pdf,.jpg,.jpeg,.png"
                      disabled={!canEdit}
                      onChange={handleFileChange}
                      className="hidden" 
                    />
                  </label>
                </div>
                
                {/* Display uploaded files */}
                {(fileInputs.length > 0 || formData.documents.length > 0) && (
                  <div className="mt-4 space-y-2">
                    {fileInputs.map((file, index) => (
                      <div key={index} className="flex items-center justify-between p-3 bg-slate-100 dark:bg-slate-700 rounded-lg">
                        <div className="flex items-center">
                          <svg className="w-5 h-5 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                          </svg>
                          <div>
                            <p className="text-sm font-medium text-slate-800 dark:text-white">{file.name}</p>
                            <p className="text-xs text-slate-500 dark:text-slate-400">{(file.size / 1024 / 1024).toFixed(2)} MB</p>
                          </div>
                        </div>
                        {canEdit && (
                          <button
                            type="button"
                            onClick={() => removeFile(index)}
                            className="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                          >
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                          </button>
                        )}
                      </div>
                    ))}
                    {formData.documents.map((doc, index) => (
                      <div key={`existing-${index}`} className="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <div className="flex items-center">
                          <svg className="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"/>
                          </svg>
                          <div>
                            <p className="text-sm font-medium text-slate-800 dark:text-white">Document {index + 1}</p>
                            <p className="text-xs text-slate-500 dark:text-slate-400">Uploaded</p>
                          </div>
                        </div>
                        <a href={doc} target="_blank" rel="noopener noreferrer" className="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                          View
                        </a>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>
          </div>
          
          {/* Action Buttons */}
          {canEdit && (
            <div className="flex justify-end space-x-3 pt-4">
              <button
                type="button"
                onClick={handleSaveDraft}
                disabled={saving || submitting}
                className="px-6 py-3 bg-slate-500 hover:bg-slate-600 text-white font-semibold rounded-lg shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {saving ? 'Saving...' : 'Save Draft'}
              </button>
              <button
                type="submit"
                disabled={saving || submitting}
                className="px-6 py-3 bg-brand-emerald-600 hover:bg-brand-emerald-700 text-white font-semibold rounded-lg shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {submitting ? 'Submitting...' : t('student_registration_submit')}
              </button>
            </div>
          )}
        </form>
      </div>
    </div>
  );
};

export default RegistrasiPage;
