import React, { useState, useMemo, useEffect } from 'react';
import { useLanguage } from '@/contexts/LanguageContext';
import { Faculty, User } from '@/types';
import { Icon } from '@/src/ui/components/Icon';
import { LoadingSpinner } from '@/src/components/shared/LoadingSpinner';
import { facultyAPI } from '@/services/apiService';

// Add declarations for CDN-loaded libraries to the global window object
declare global {
    interface Window {
        jspdf: any;
        XLSX: any;
    }
}

interface ManajemenFakultasPageProps {
    faculties: Faculty[];
    setFaculties: (faculties: Faculty[]) => void;
    users: User[];
}

export const ManajemenFakultasPage: React.FC<ManajemenFakultasPageProps> = ({ faculties, setFaculties, users }) => {
    const { t } = useLanguage();
    const [searchTerm, setSearchTerm] = useState('');
    const [showAddForm, setShowAddForm] = useState(false);
    const [showEditForm, setShowEditForm] = useState(false);
    const [editingFaculty, setEditingFaculty] = useState<Faculty | null>(null);
    const [newFaculty, setNewFaculty] = useState({
        id: '',
        code: '',
        name: '',
        description: '',
        majors: []
    });
    const [loading, setLoading] = useState(true); // Start with true to show loading on initial render
    const [error, setError] = useState<string | null>(null);

    // Fetch faculties from API on component mount
    useEffect(() => {
        fetchFaculties();
    }, []);

    const fetchFaculties = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = await facultyAPI.getAll();
            // API service now handles unwrapping the backend response
            // Backend now returns faculties with majors included via eager loading
            const facultiesData = Array.isArray(response.data) ? response.data : [];
            const facultiesWithMajors = facultiesData.map((faculty: Faculty) => ({
                ...faculty,
                majors: faculty.majors || []
            }));
            setFaculties(facultiesWithMajors);
        } catch (err) {
            console.error('Error fetching faculties:', err);
            setError('Gagal memuat data fakultas');
        } finally {
            setLoading(false);
        }
    };

    const filteredFaculties = useMemo(() => {
        return faculties.filter(faculty =>
            faculty.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            faculty.description.toLowerCase().includes(searchTerm.toLowerCase())
        );
    }, [faculties, searchTerm]);

    const handleExportPDF = () => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        doc.text("Daftar Fakultas", 14, 16);
        (doc as any).autoTable({
            startY: 22,
            head: [['Nama Fakultas', 'Deskripsi', 'Jumlah Prodi']],
            body: filteredFaculties.map(f => [f.name, f.description, f.majors?.length || 0]),
        });
        doc.save('daftar-fakultas.pdf');
    };

    const handleExportXLSX = () => {
        const worksheet = window.XLSX.utils.json_to_sheet(
            filteredFaculties.map(f => ({
                'Nama Fakultas': f.name,
                'Deskripsi': f.description,
                'Jumlah Prodi': f.majors?.length || 0,
            }))
        );
        const workbook = window.XLSX.utils.book_new();
        window.XLSX.utils.book_append_sheet(workbook, worksheet, 'Fakultas');
        window.XLSX.writeFile(workbook, 'daftar-fakultas.xlsx');
    };

    const handleAddFaculty = async () => {
        if (newFaculty.name && newFaculty.code) {
            try {
                setLoading(true);
                setError(null);
                const facultyId = newFaculty.code.toLowerCase().replace(/\s+/g, '-');
                const response = await facultyAPI.create({
                    id: facultyId,
                    code: newFaculty.code,
                    name: newFaculty.name,
                    description: newFaculty.description,
                    is_active: true
                } as Partial<Faculty>);
                // API service now handles unwrapping the backend response
                // Ensure majors array is initialized
                const createdFaculty = {
                    ...response.data,
                    majors: response.data.majors || []
                };
                setFaculties([...faculties, createdFaculty]);
                setNewFaculty({ id: '', code: '', name: '', description: '', majors: [] });
                setShowAddForm(false);
            } catch (err) {
                console.error('Error adding faculty:', err);
                setError('Gagal menambahkan fakultas');
            } finally {
                setLoading(false);
            }
        }
    };

    const handleUpdateFaculty = async () => {
        if (editingFaculty && editingFaculty.id) {
            try {
                setLoading(true);
                setError(null);
                await facultyAPI.update(editingFaculty.id, {
                    code: editingFaculty.code || editingFaculty.id,
                    name: editingFaculty.name,
                    description: editingFaculty.description
                });
                const updatedFaculties = faculties.map(faculty => 
                    faculty.id === editingFaculty.id ? editingFaculty : faculty
                );
                setFaculties(updatedFaculties);
                setEditingFaculty(null);
                setShowEditForm(false);
            } catch (err) {
                console.error('Error updating faculty:', err);
                setError('Gagal mengupdate fakultas');
            } finally {
                setLoading(false);
            }
        }
    };

    const handleDeleteFaculty = async (id: string) => {
        if (window.confirm('Apakah Anda yakin ingin menghapus fakultas ini?')) {
            // Check if any users are assigned to this faculty
            const usersInFaculty = users.filter(user => user.facultyId === id);
            if (usersInFaculty.length > 0) {
                alert('Tidak dapat menghapus fakultas karena masih terdapat pengguna yang terdaftar dalam fakultas ini.');
                return;
            }
            try {
                setLoading(true);
                setError(null);
                await facultyAPI.delete(id);
                setFaculties(faculties.filter(faculty => faculty.id !== id));
            } catch (err) {
                console.error('Error deleting faculty:', err);
                setError('Gagal menghapus fakultas');
            } finally {
                setLoading(false);
            }
        }
    };

    const startEditing = (faculty: Faculty) => {
        setEditingFaculty({ ...faculty });
        setShowEditForm(true);
    };

    return (
        <div className="space-y-8">
            {/* Loading indicator - show when loading */}
            {loading ? (
                <LoadingSpinner />
            ) : (
                <>
                    {/* Page header - show only after loading */}
                    <div>
                        <h1 className="text-3xl font-bold text-slate-800 dark:text-white">Manajemen Fakultas</h1>
                        <p className="text-slate-500 dark:text-slate-400 mt-1">Kelola fakultas di UlumCampus.</p>
                    </div>

                    {/* Error message */}
                    {error && (
                        <div className="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg">
                            {error}
                        </div>
                    )}

                    {/* Main content */}
                    <div className="bg-white dark:bg-slate-800/50 p-6 rounded-2xl shadow-md">
                <div className="flex flex-col sm:flex-row items-start sm:items-center gap-4 mb-4">
                    <div className="relative flex-grow w-full sm:w-auto">
                        <Icon className="absolute start-3 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5">
                            <circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" />
                        </Icon>
                        <input 
                            type="text" 
                            placeholder="Cari nama atau deskripsi fakultas..." 
                            value={searchTerm} 
                            onChange={e => setSearchTerm(e.target.value)} 
                            className="w-full ps-10 pe-4 py-2 rounded-full bg-slate-100 dark:bg-slate-700 border border-transparent focus:outline-none focus:ring-2 focus:ring-brand-emerald-500 text-slate-800 dark:text-white" 
                        />
                    </div>
                    <div className="flex-grow flex justify-start sm:justify-end items-center gap-2 w-full sm:w-auto">
                        <button onClick={handleExportPDF} className="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/50 rounded-full transition-colors" title="Export PDF">
                            <Icon className="w-5 h-5"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M10 12v-1h3v1"/><path d="M10 15h3"/><path d="M10 18h3"/></Icon>
                        </button>
                        <button onClick={handleExportXLSX} className="p-2 text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/50 rounded-full transition-colors" title="Export XLSX">
                            <Icon className="w-5 h-5"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M12 18v-4_M15 14h-3_M9 14h3"/><path d="M10.5 10.5 13.5 7.5_M13.5 10.5 10.5 7.5"/></Icon>
                        </button>
                        <button 
                            onClick={() => {
                                setNewFaculty({ id: '', code: '', name: '', description: '', majors: [] });
                                setShowAddForm(true);
                            }}
                            className="px-4 py-2 bg-brand-emerald-600 text-white font-semibold rounded-lg hover:bg-brand-emerald-700 transition-colors flex items-center gap-2"
                        >
                            <Icon className="w-5 h-5"><path d="M12 5v14M5 12h14"/></Icon>
                            Tambah Fakultas
                        </button>
                    </div>
                </div>
                <div className="overflow-x-auto">
                    <table className="w-full text-sm text-left text-slate-500 dark:text-slate-400">
                        <thead className="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-300">
                            <tr>
                                <th scope="col" className="px-6 py-3">Nama Fakultas</th>
                                <th scope="col" className="px-6 py-3">Deskripsi</th>
                                <th scope="col" className="px-6 py-3">Jumlah Prodi</th>
                                <th scope="col" className="px-6 py-3">Tanggal Dibuat</th>
                                <th scope="col" className="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {filteredFaculties.map(faculty => (
                                <tr key={faculty.id} className="bg-white border-b dark:bg-slate-800 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600/50">
                                    <td className="px-6 py-4 font-medium text-slate-900 dark:text-white">
                                        {faculty.name}
                                    </td>
                                    <td className="px-6 py-4">{faculty.description}</td>
                                    <td className="px-6 py-4">{faculty.majors?.length || 0}</td>
                                    <td className="px-6 py-4">
                                        {faculty.createdAt ? new Date(faculty.createdAt).toLocaleDateString('id-ID') : '-'}
                                    </td>
                                    <td className="px-6 py-4 flex space-x-2">
                                        <button 
                                            onClick={() => startEditing(faculty)}
                                            className="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                        >
                                            Edit
                                        </button>
                                        <button 
                                            onClick={() => handleDeleteFaculty(faculty.id)}
                                            className="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                        >
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
                </>
            )}

            {/* Add Faculty Modal */}
            {showAddForm && (
                <div className="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-md">
                        <div className="p-6">
                            <div className="flex justify-between items-center mb-4">
                                <h3 className="text-lg font-semibold text-slate-800 dark:text-white">
                                    Tambah Fakultas Baru
                                </h3>
                                <button 
                                    onClick={() => setShowAddForm(false)}
                                    className="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
                                >
                                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <div className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kode Fakultas</label>
                                    <input
                                        type="text"
                                        value={newFaculty.code}
                                        onChange={(e) => setNewFaculty({...newFaculty, code: e.target.value})}
                                        className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white"
                                        placeholder="Contoh: FK, FT, FIK"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Fakultas</label>
                                    <input
                                        type="text"
                                        value={newFaculty.name}
                                        onChange={(e) => setNewFaculty({...newFaculty, name: e.target.value})}
                                        className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white"
                                        placeholder="Masukkan nama fakultas"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Deskripsi</label>
                                    <input
                                        type="text"
                                        value={newFaculty.description}
                                        onChange={(e) => setNewFaculty({...newFaculty, description: e.target.value})}
                                        className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white"
                                        placeholder="Masukkan deskripsi fakultas"
                                    />
                                </div>
                                <div className="flex space-x-2">
                                    <button
                                        onClick={handleAddFaculty}
                                        className="flex-1 px-4 py-2 bg-brand-emerald-600 hover:bg-brand-emerald-700 text-white rounded-lg transition-colors"
                                    >
                                        Simpan
                                    </button>
                                    <button
                                        onClick={() => setShowAddForm(false)}
                                        className="flex-1 px-4 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-white rounded-lg transition-colors"
                                    >
                                        Batal
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* Edit Faculty Modal */}
            {showEditForm && editingFaculty && (
                <div className="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-md">
                        <div className="p-6">
                            <div className="flex justify-between items-center mb-4">
                                <h3 className="text-lg font-semibold text-slate-800 dark:text-white">
                                    Edit Fakultas
                                </h3>
                                <button 
                                    onClick={() => {
                                        setShowEditForm(false);
                                        setEditingFaculty(null);
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
                                    <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kode Fakultas</label>
                                    <input
                                        type="text"
                                        value={editingFaculty.code || editingFaculty.id}
                                        onChange={(e) => setEditingFaculty({...editingFaculty, code: e.target.value})}
                                        className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white"
                                        placeholder="Contoh: FK, FT, FIK"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Fakultas</label>
                                    <input
                                        type="text"
                                        value={editingFaculty.name}
                                        onChange={(e) => setEditingFaculty({...editingFaculty, name: e.target.value})}
                                        className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white"
                                        placeholder="Masukkan nama fakultas"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Deskripsi</label>
                                    <input
                                        type="text"
                                        value={editingFaculty.description}
                                        onChange={(e) => setEditingFaculty({...editingFaculty, description: e.target.value})}
                                        className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white"
                                        placeholder="Masukkan deskripsi fakultas"
                                    />
                                </div>
                                <div className="flex space-x-2">
                                    <button
                                        onClick={handleUpdateFaculty}
                                        className="flex-1 px-4 py-2 bg-brand-emerald-600 hover:bg-brand-emerald-700 text-white rounded-lg transition-colors"
                                    >
                                        Simpan Perubahan
                                    </button>
                                    <button
                                        onClick={() => {
                                            setShowEditForm(false);
                                            setEditingFaculty(null);
                                        }}
                                        className="flex-1 px-4 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-white rounded-lg transition-colors"
                                    >
                                        Batal
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};
