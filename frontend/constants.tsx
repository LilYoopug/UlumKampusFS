import React from 'react';
import { Faculty, Course, User, Badge, LibraryResource, Assignment, Submission, AcademicCalendarEvent, DiscussionThread, Notification, Announcement } from './types';
import { Icon } from './src/ui/components/Icon';

export const MAHASISWA_USER: User = {
  name: 'Ahmad Faris',
  avatarUrl: 'https://picsum.photos/seed/ahmad/100/100',
  role: 'Mahasiswa',
  studentId: 'UC2024001',
  email: 'ahmad.faris@student.ulumcampus.com',
  phoneNumber: '081234567890',
  joinDate: '2023-09-01',
  bio: 'Penuntut ilmu syar\'i dari Jakarta yang bersemangat mempelajari Fiqh Muamalat dan Sejarah Peradaban Islam untuk berkontribusi pada kemajuan umat.',
  studentStatus: 'Aktif',
  gpa: 3.85,
  totalSks: 42,
  facultyId: 'syariah',
  majorId: 'hes',
};
export const DOSEN_USER: User = {
  name: 'Dr. Yusuf Al-Fatih',
  avatarUrl: 'https://picsum.photos/seed/yusuf/100/100',
  role: 'Dosen',
  studentId: 'DSN202001',
  email: 'yusuf.alfatih@dosen.ulumcampus.com',
  phoneNumber: '081234567891',
  joinDate: '2020-01-15',
  bio: 'Akademisi dan da\'i yang fokus pada studi Aqidah dan Manhaj. Meraih gelar Doktor dari Universitas Islam Madinah.',
};

export const PRODI_ADMIN_USER: User = {
  name: 'Dr. Aisyah Hasanah',
  avatarUrl: 'https://picsum.photos/seed/aisyah/100/100',
  role: 'Prodi Admin',
  studentId: 'PRODI01',
  email: 'aisyah.h@staff.ulumcampus.com',
  phoneNumber: '081234567892',
  joinDate: '2019-08-20',
  bio: 'Kepala Program Studi Syariah & Hukum. Pakar Fiqh Muamalat dan Ekonomi Syariah. Lulusan S1 Fiqh dari Universitas Al-Azhar, Kairo.',
  facultyId: 'syariah',
};

export const MANAJEMEN_KAMPUS_USER: User = {
 name: 'Prof. Dr. Ibrahim Malik',
  avatarUrl: 'https://picsum.photos/seed/ibrahim/100/100',
  role: 'Manajemen Kampus',
  studentId: 'REKTOR01',
  email: 'rektor@ulumcampus.com',
  phoneNumber: '081234567893',
  joinDate: '2015-03-10',
  bio: 'Rektor UlumCampus. Guru besar di bidang Keuangan Syariah dengan pengalaman lebih dari 20 tahun.',
};

export const MABA_USER: User = {
  name: 'Budi Santoso',
  avatarUrl: 'https://picsum.photos/seed/budi/100/100',
  role: 'MABA',
  studentId: 'MABA2025001',
  email: 'budi.santoso@maba.ulumcampus.com',
  phoneNumber: '081234567899',
  joinDate: '2025-01-10',
  bio: 'Mahasiswa baru yang antusias belajar di UlumCampus. Tertarik dengan studi Fiqh Muamalat dan Ekonomi Syariah.',
  studentStatus: 'Pendaftaran',
  gpa: 0,
  totalSks: 0,
  facultyId: 'syariah',
  majorId: 'hes',
};

export const SUPER_ADMIN_USER: User = {
    name: 'Admin Sistem',
    avatarUrl: 'https://picsum.photos/seed/admin/100/100',
    role: 'Super Admin',
    studentId: 'SYSADMIN',
    email: 'admin@ulumcampus.com',
    phoneNumber: '081234567894',
    joinDate: '2014-01-01',
    bio: 'Administrator sistem utama UlumCampus. Bertanggung jawab atas infrastruktur teknis dan manajemen pengguna.',
};

export const MAHASISWA_PASSWORD = 'mahasiswa123';

export const DOSEN_PASSWORD = 'dosen123';

export const PRODI_ADMIN_PASSWORD = 'prodi123';

export const MANAJEMEN_KAMPUS_PASSWORD = 'manajemen123';

export const MABA_PASSWORD = 'maba123';
export const SUPER_ADMIN_PASSWORD = 'admin123';

export const ALL_USERS: User[] = [
  MAHASISWA_USER,
  DOSEN_USER,
  PRODI_ADMIN_USER,
  MANAJEMEN_KAMPUS_USER,
  MABA_USER,
  SUPER_ADMIN_USER,
  {
    name: 'Dr. Eng. Faiz Rabbani',
    avatarUrl: 'https://picsum.photos/seed/faiz/100/100',
    role: 'Dosen',
    studentId: 'DSN202105',
    email: 'faiz.rabbani@dosen.ulumcampus.com',
    phoneNumber: '081234567895',
    joinDate: '2021-01-10',
    bio: 'Insinyur dan peneliti yang menjembatani dunia teknologi dan studi Islam. Lulusan doktor teknik dari Jepang ini memimpin sebuah lab riset yang fokus mengembangkan aplikasi AI untuk kemaslahatan umat.',
  },
  { name: 'Siti Maryam', avatarUrl: 'https://picsum.photos/seed/maryam/100/100', role: 'Mahasiswa', studentId: 'UC2024002', email: 'siti.m@student.ulumcampus.com', phoneNumber: '081234567896', joinDate: '2023-09-01', bio: '', studentStatus: 'Aktif', gpa: 3.92, totalSks: 42, facultyId: 'syariah', majorId: 'ahwal-syakhshiyyah' },
  { name: 'Abdullah', avatarUrl: 'https://picsum.photos/seed/abdullah/100/100', role: 'Mahasiswa', studentId: 'UC2024003', email: 'abdullah@student.ulumcampus.com', phoneNumber: '081234567897', joinDate: '2023-09-01', bio: '', studentStatus: 'Cuti', gpa: 3.50, totalSks: 28, facultyId: 'syariah', majorId: 'hes' },
];

export const USER_PASSWORDS: Record<string, string> = {
  'ahmad.faris@student.ulumcampus.com': MAHASISWA_PASSWORD,
  'yusuf.alfatih@dosen.ulumcampus.com': DOSEN_PASSWORD,
  'aisyah.h@staff.ulumcampus.com': PRODI_ADMIN_PASSWORD,
  'rektor@ulumcampus.com': MANAJEMEN_KAMPUS_PASSWORD,
  'budi.santoso@maba.ulumcampus.com': MABA_PASSWORD,
  'admin@ulumcampus.com': SUPER_ADMIN_PASSWORD,
  'faiz.rabbani@dosen.ulumcampus.com': DOSEN_PASSWORD,
  'siti.m@student.ulumcampus.com': MAHASISWA_PASSWORD,
  'abdullah@student.ulumcampus.com': MAHASISWA_PASSWORD,
};

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

export const FACULTIES: Faculty[] = [
    { 
        id: 'ushuluddin', 
        name: 'Ushuluddin & Dakwah', 
        description: 'Studi fundamental keimanan dan metode dakwah.',
        majors: [
          { id: 'aqidah', name: 'Aqidah & Filsafat' },
          { id: 'tafsir', name: 'Ilmu Al-Qur\'an & Tafsir' },
          { id: 'hadis', name: 'Ilmu Hadis' },
          { id: 'perbandingan-agama', name: 'Perbandingan Agama' },
          { id: 'kpi', name: 'KPI (Dakwah Digital)' },
        ],
        createdAt: '2020-01-15',
    },
    { 
        id: 'syariah', 
        name: 'Syariah & Hukum', 
        description: 'Kajian hukum Islam dan aplikasinya dalam kehidupan.',
        majors: [
          { id: 'hes', name: 'HES (Muamalat)' },
          { id: 'ahwal-syakhshiyyah', name: 'Ahwal Syakhshiyyah' },
          { id: 'siyasah', name: 'Siyasah' },
          { id: 'peradilan-agama', name: 'Peradilan Agama/Arbitrase Syariah' },
        ],
        createdAt: '2020-01-15',
    },
    { 
        id: 'ekonomi', 
        name: 'Ekonomi & Manajemen Syariah', 
        description: 'Prinsip ekonomi dan bisnis berbasis syariah.',
        majors: [
            { id: 'ekonomi-islam', name: 'Ekonomi Islam' },
            { id: 'perbankan-syariah', name: 'Perbankan Syariah' },
            { id: 'akuntansi-syariah', name: 'Akuntansi Syariah' },
            { id: 'manajemen-syariah', name: 'Manajemen Syariah' },
            { id: 'keuangan-investasi-syariah', name: 'Keuangan & Investasi Syariah' },
        ],
        createdAt: '2020-01-15',
    },
    { 
        id: 'tarbiyah', 
        name: 'Tarbiyah & Pendidikan Islam', 
        description: 'Ilmu mendidik dan membentuk generasi Islami.',
        majors: [
            { id: 'pai', name: 'PAI' },
            { id: 'pba', name: 'PBA' },
            { id: 'pgmi', name: 'PGMI' },
            { id: 'mpi', name: 'MPI' },
            { id: 'tekpen-islami', name: 'TekPen Islami' },
        ],
        createdAt: '2020-01-15',
    },
    {
        id: 'adab',
        name: 'Adab, Humaniora & Bahasa',
        description: 'Studi peradaban, sastra, dan bahasa dalam konteks Islam.',
        majors: [
            { id: 'spi', name: 'SPI' },
            { id: 'bsa', name: 'BSA' },
            { id: 'english-islamic', name: 'English for Islamic Studies' },
            { id: 'islamic-civ', name: 'Islamic Civilization' },
        ],
        createdAt: '2020-01-15',
    },
    {
        id: 'sains',
        name: 'Sains & Inovasi Islami',
        description: 'Integrasi sains dan teknologi dengan etika dan nilai-nilai Islam.',
        majors: [
            { id: 'sains-etika', name: 'Sains & Etika Syariah' },
            { id: 'ti-islami', name: 'TI Islami (AI/Apps)' },
            { id: 'industri-halal', name: 'Teknik Industri Halal & SC' },
            { id: 'farmasi-halal', name: 'Farmasi Halal' },
            { id: 'kesehatan-syariah', name: 'Kesehatan Syariah' },
        ],
        createdAt: '2021-08-20',
    },
    {
        id: 'psikologi',
        name: 'Psikologi & Sosial',
        description: 'Kajian perilaku manusia dan masyarakat dari perspektif Islam.',
        majors: [
            { id: 'psikologi-islam', name: 'Psikologi Islam' },
            { id: 'bk-islami', name: 'BK Islami' },
            { id: 'sosiologi-islam', name: 'Sosiologi Islam' },
            { id: 'studi-gender', name: 'Studi Gender dalam Islam' },
        ],
        createdAt: '2022-01-10',
    },
    {
        id: 'pascasarjana',
        name: 'Sekolah Pascasarjana',
        description: 'Studi lanjutan untuk kajian Islam kontemporer dan kepemimpinan.',
         majors: [
            { id: 'kajian-kontemporer', name: 'Kajian Islam Kontemporer' },
            { id: 'fiqh-aqalliyat', name: 'Fiqh al-Aqalliyat' },
            { id: 'islamic-leadership', name: 'Islamic Leadership & Da\'wah Management' },
        ],
        createdAt: '2021-01-15',
    }
];

const now = new Date();
const getFutureDate = (days: number, hour: number, minute: number) => {
    const date = new Date();
    date.setDate(date.getDate() + days);
    date.setHours(hour, minute, 0, 0);
    return date.toISOString();
}

const getPastDate = (daysAgo: number) => {
    const date = new Date();
    const hoursAgo = (daysAgo - Math.floor(daysAgo)) * 24;
    date.setDate(date.getDate() - Math.floor(daysAgo));
    date.setHours(date.getHours() - hoursAgo);
    return date.toISOString();
};

const getYearDate = (year: number, month: number, day: number) => {
    const date = new Date();
    date.setFullYear(year, month - 1, day);
    date.setHours(0, 0, 0, 0);
    return date.toISOString();
}

let initialCourses: Course[] = [
  {
    id: 'AQ101',
    title: 'Pengantar Aqidah Islamiyah',
    instructor: 'Dr. Yusuf Al-Fatih',
    instructorAvatarUrl: 'https://picsum.photos/seed/yusuf/100/100',
    instructorBioKey: 'bio_yusuf_al_fatih',
    facultyId: 'ushuluddin',
    majorId: 'aqidah',
    sks: 3,
    description: 'Membahas pilar-pilar fundamental keimanan dalam Islam berdasarkan Al-Qur\'an dan Sunnah dengan pemahaman salaful ummah. Kursus ini mencakup tauhid, kenabian, hari akhir, dan takdir.',
    imageUrl: 'https://picsum.photos/seed/aqidah/600/400',
    progress: 75,
    mode: 'VOD',
    status: 'Published',
    learningObjectives: [
        'Mampu menjelaskan pilar-pilar fundamental keimanan dalam Islam.',
        'Memahami konsep Tauhid dan pembagiannya secara komprehensif.',
        'Dapat membedakan antara keyakinan yang lurus dengan penyimpangan.',
        'Menginternalisasi konsekuensi dari syahadatain dalam kehidupan sehari-hari.'
    ],
    syllabus: [
        { week: 1, topic: 'Pengantar Ilmu Aqidah', description: 'Definisi, urgensi, dan sumber-sumber utama dalam mempelajari aqidah Islamiyah.' },
        { week: 2, topic: 'Makna dan Konsekuensi Syahadatain', description: 'Analisis mendalam tentang rukun, syarat, dan pembatal dua kalimat syahadat.' },
        { week: 3, topic: 'Konsep Tauhid dan Pembagiannya', description: 'Pembahasan Tauhid Rububiyah, Uluhiyah, dan Asma wa Sifat beserta dalil-dalilnya.' },
        { week: 4, topic: 'Keimanan kepada Malaikat, Kitab, dan Rasul', description: 'Mempelajari hakikat, nama, dan tugas-tugas malaikat serta kewajiban beriman kepada kitab-kitab dan para rasul Allah.' },
    ],
    modules: [
      { id: 'm1', title: 'Makna Syahadatain', type: 'video', duration: '45min', description: 'Membedah makna dan konsekuensi dari dua kalimat syahadat sebagai fondasi utama keislaman.', resourceUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4', captionsUrl: 'https://gist.githubusercontent.com/samdutton/ca37f3adaf4e23679957b8083e061177/raw/e19399addb3b8b548c7c71f085185a06065b7a39/sintel-en.vtt' },
      { id: 'm2', title: 'Pembagian Tauhid', type: 'video', duration: '55min', description: 'Penjelasan rinci mengenai Tauhid Rububiyah, Uluhiyah, dan Asma wa Sifat beserta dalil-dalilnya.', resourceUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4' },
      { id: 'm3', title: 'Rukun Iman', type: 'pdf', description: 'Dokumen ini berisi ringkasan komprehensif dari enam rukun iman, lengkap dengan dalil-dalil utama dari Al-Qur\'an dan Sunnah.', resourceUrl: '#' },
    ]
  },
  {
    id: 'FQ201',
    title: 'Fiqh Muamalat Kontemporer',
    instructor: 'Dr. Aisyah Hasanah',
    instructorAvatarUrl: 'https://picsum.photos/seed/aisyah/100/100',
    instructorBioKey: 'bio_aisyah_hasanah',
    facultyId: 'syariah',
    majorId: 'hes',
    sks: 4,
    description: 'Analisis transaksi keuangan modern dari perspektif fiqh. Meliputi pembahasan perbankan syariah, asuransi, pasar modal, dan fintech sesuai prinsip-prinsip syariah.',
    imageUrl: 'https://picsum.photos/seed/muamalat/600/400',
    progress: 100,
    gradeLetter: 'A-',
    gradeNumeric: 91,
    completionDate: '2024-06-15',
    mode: 'VOD',
    status: 'Published',
    learningObjectives: [
      'Mampu mengidentifikasi unsur Riba, Gharar, dan Maysir dalam transaksi modern.',
      'Memahami skema akad-akad utama dalam produk perbankan syariah.',
      'Menganalisis isu-isu kontemporer dalam fintech syariah.',
      'Menerapkan kaidah-kaidah fiqh muamalat dalam studi kasus.'
    ],
    syllabus: [
        { week: 1, topic: 'Kaidah Fiqh Muamalat', description: 'Mempelajari kaidah-kaidah kunci seperti "Al-ashlu fil mu\'amalah al-ibahah" (Hukum asal dalam muamalah adalah boleh).' },
        { week: 2, topic: 'Riba dan Gharar', description: 'Definisi, jenis-jenis, dan bahaya Riba serta ketidakpastian (Gharar) dalam transaksi modern.' },
        { week: 3, topic: 'Akad Jual Beli (Al-Bai\')', description: 'Pembahasan berbagai jenis akad jual beli seperti Murabahah, Salam, dan Istisna\'.' },
        { week: 4, topic: 'Akad Kemitraan (Syirkah)', description: 'Mempelajari konsep Mudharabah dan Musyarakah serta aplikasinya dalam bisnis dan investasi.' },
    ],
     modules: [
      { id: 'm1', title: 'Pengantar Fiqh Muamalat', type: 'video', duration: '50min', description: 'Memahami kaidah-kaidah dasar dan prinsip umum dalam transaksi maliyah Islam.', resourceUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4' },
      { id: 'm2', title: 'Akad-akad dalam Transaksi', type: 'pdf', description: 'Materi bacaan mendalam yang membahas berbagai jenis akad dalam transaksi maliyah, termasuk syarat, rukun, dan contoh aplikasinya.', resourceUrl: '#' },
      { id: 'm3', title: 'Studi Kasus: Fintech Syariah', type: 'video', duration: '60min', description: 'Analisis studi kasus mengenai aplikasi dan tantangan fiqh muamalat pada platform fintech syariah modern.', resourceUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4' },
    ]
  },
  {
    id: 'EK301',
    title: 'Manajemen Keuangan Syariah',
    instructor: 'Prof. Dr. Ibrahim Malik',
    instructorAvatarUrl: 'https://picsum.photos/seed/ibrahim/100/100',
    instructorBioKey: 'bio_ibrahim_malik',
    facultyId: 'ekonomi',
    majorId: 'keuangan-investasi-syariah',
    sks: 3,
    description: 'Mempelajari prinsip dan praktik manajemen keuangan pada lembaga keuangan syariah, termasuk manajemen likuiditas, risiko, dan investasi halal.',
    imageUrl: 'https://picsum.photos/seed/keuangan/600/400',
    progress: 95,
    mode: 'Live',
    status: 'Published',
    learningObjectives: [
      'Memahami perbedaan fundamental antara manajemen keuangan syariah dan konvensional.',
      'Mampu menganalisis laporan keuangan lembaga keuangan syariah.',
      'Mengidentifikasi dan mengelola risiko-risiko spesifik dalam keuangan syariah.',
      'Menyusun perencanaan investasi yang sesuai dengan prinsip syariah.'
    ],
    syllabus: [
        { week: 1, topic: 'Prinsip Dasar Keuangan Islam', description: 'Membedah filosofi dan tujuan (Maqashid Syariah) dari sistem keuangan Islam.' },
        { week: 2, topic: 'Manajemen Aset dan Liabilitas LKS', description: 'Teknik mengelola aset dan liabilitas pada Lembaga Keuangan Syariah (LKS) untuk menjaga likuiditas dan profitabilitas.' },
        { week: 3, topic: 'Manajemen Risiko Keuangan Syariah', description: 'Identifikasi dan mitigasi berbagai jenis risiko, termasuk risiko kredit, pasar, dan operasional yang spesifik bagi LKS.' },
    ],
     modules: [
      { id: 'm1', title: 'Dasar-dasar Keuangan Islam', type: 'video', duration: '48min', description: 'Pengenalan prinsip dasar, larangan, dan tujuan (maqashid) dari sistem keuangan Islam.', resourceUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4' },
      { id: 'm2', title: 'Manajemen Aset & Liabilitas', type: 'pdf', description: 'Penjelasan rinci mengenai teknik-teknik manajemen aset dan liabilitas pada Lembaga Keuangan Syariah (LKS) untuk menjaga likuiditas dan profitabilitas.', resourceUrl: '#' },
      { 
        id: 'm_live_1', 
        title: 'Sesi Live: Manajemen Likuiditas', 
        type: 'live', 
        description: 'Sesi tanya jawab dan diskusi mendalam tentang manajemen likuiditas pada lembaga keuangan syariah.',
        startTime: getFutureDate(3, 10, 0),
        liveUrl: 'https://meet.google.com/abc-defg-hij'
      }
    ]
  },
  {
    id: 'TR401',
    title: 'Metodologi Pengajaran PAI',
    instructor: 'Dr. Yusuf Al-Fatih', // Changed instructor for 'my courses' demo
    instructorAvatarUrl: 'https://picsum.photos/seed/yusuf/100/100',
    instructorBioKey: 'bio_yusuf_al_fatih',
    facultyId: 'tarbiyah',
    majorId: 'pai',
    sks: 3,
    description: 'Kursus ini membekali calon pendidik dengan berbagai metode dan strategi pengajaran Pendidikan Agama Islam (PAI) yang efektif dan relevan untuk generasi milenial dan Z.',
    imageUrl: 'https://picsum.photos/seed/tarbiyah/600/400',
    progress: 10,
    mode: 'VOD',
    status: 'Draft',
    learningObjectives: [
      'Merancang Rencana Pelaksanaan Pembelajaran (RPP) PAI yang inovatif.',
      'Menerapkan berbagai model pembelajaran aktif dalam kelas PAI.',
      'Mengembangkan media pembelajaran PAI berbasis teknologi.',
      'Melakukan evaluasi pembelajaran yang otentik dan bermakna.'
    ],
    syllabus: [
        { week: 1, topic: 'Filosofi dan Tujuan Pendidikan Islam', description: 'Memahami landasan filosofis pendidikan dalam Islam untuk membentuk insan kamil.' },
        { week: 2, topic: 'Desain Kurikulum dan Pembelajaran PAI', description: 'Praktik merancang silabus, RPP, dan materi ajar PAI yang sesuai dengan perkembangan peserta didik.' },
    ],
     modules: [
      { id: 'm1', title: 'Filosofi Pendidikan Islam', type: 'video', duration: '52min', description: 'Kajian mendalam tentang landasan filosofis dan tujuan akhir dari pendidikan dalam Islam (Tarbiyah Islamiyah).', resourceUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4' },
      { id: 'm2', title: 'Model Pembelajaran Aktif', type: 'pdf', description: 'Panduan praktis mengenai penerapan model pembelajaran aktif seperti Problem-Based Learning dan Project-Based Learning dalam konteks PAI.', resourceUrl: '#' },
    ]
  },
  {
    id: 'HD202',
    title: 'Kritik Sanad dan Matan Hadis',
    instructor: 'Dr. Abdullah Musnad',
    instructorAvatarUrl: 'https://picsum.photos/seed/abdullah/100/100',
    instructorBioKey: 'bio_abdullah_musnad',
    facultyId: 'ushuluddin',
    majorId: 'hadis',
    sks: 3,
    description: 'Mempelajari metodologi ulama hadis dalam melakukan kritik (naqd) terhadap sanad (rantai perawi) dan matan (isi) hadis untuk menentukan otentisitasnya.',
    imageUrl: 'https://picsum.photos/seed/hadis/600/400',
    progress: 25,
    mode: 'VOD',
    status: 'Published',
    learningObjectives: [
        'Memahami syarat-syarat kesahihan sanad hadis.',
        'Mengenal kitab-kitab rujukan utama dalam ilmu rijal al-hadis.',
        'Mampu melakukan kritik eksternal (sanad) dan internal (matan) pada tingkat dasar.',
        'Mengidentifikasi sebab-sebab cacatnya sebuah hadis.'
    ],
    syllabus: [
        { week: 1, topic: 'Pengantar Ilmu Musthalah al-Hadis', description: 'Mengenal istilah-istilah kunci dalam ilmu hadis seperti sanad, matan, shahih, hasan, dan dhaif.' },
        { week: 2, topic: 'Ilmu Rijal al-Hadis (Kritik Perawi)', description: 'Mempelajari metodologi untuk menilai kredibilitas dan kapasitas seorang perawi hadis (al-jarh wa at-ta\'dil).' },
        { week: 3, topic: 'Ilal al-Hadis (Cacat Tersembunyi)', description: 'Mendeteksi cacat-cacat tersembunyi dalam sanad atau matan yang hanya dapat diidentifikasi oleh para ahli hadis.' },
    ],
    modules: [
      { id: 'm1', title: 'Pengantar Ilmu Rijal al-Hadis', type: 'video', duration: '60min', description: 'Video ini menjelaskan urgensi ilmu rijal (biografi perawi) dan konsep al-jarh wa at-ta\'dil (kritik dan pujian) sebagai fondasi kritik sanad.', resourceUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/Sintel.mp4' },
      { id: 'm2', title: 'Syarat-syarat Sanad yang Sahih', type: 'pdf', description: 'Dokumen yang merinci lima syarat utama kesahihan sanad sebuah hadis, yaitu: bersambungnya sanad, keadilan perawi, kedhabitan perawi, tidak adanya syadz (kejanggalan), dan tidak adanya \'illah (cacat tersembunyi).', resourceUrl: '#' },
      { id: 'm3', title: 'Metode Kritik Matan', type: 'video', duration: '50min', description: 'Pembahasan mengenai bagaimana ulama hadis mengkritik isi (matan) hadis dengan membandingkannya dengan Al-Qur\'an, hadis lain yang lebih kuat, dan fakta sejarah.', resourceUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/SubaruOutbackOnStreetAndDirt.mp4' },
    ]
  },
  {
    id: 'EK305',
    title: 'Akad dan Produk Perbankan Syariah',
    instructor: 'Dr. Halimah Sa\'diyah, M.E.I',
    instructorAvatarUrl: 'https://picsum.photos/seed/halimah/100/100',
    instructorBioKey: 'bio_halimah_sadiyah',
    facultyId: 'ekonomi',
    majorId: 'perbankan-syariah',
    sks: 3,
    description: 'Mendalami berbagai jenis akad (mudharabah, musyarakah, murabahah, ijarah) dan implementasinya dalam produk-produk perbankan syariah modern.',
    imageUrl: 'https://picsum.photos/seed/perbankan/600/400',
    progress: 0,
    mode: 'Live',
    status: 'Published',
    learningObjectives: [
        'Membedakan antara akad tabarru\' dan tijarah.',
        'Menjelaskan mekanisme operasional produk funding berbasis akad wadiah dan mudharabah.',
        'Menjelaskan mekanisme operasional produk financing berbasis jual beli, sewa, dan bagi hasil.',
        'Menganalisis inovasi produk perbankan syariah dari sisi kesesuaian akad.'
    ],
    syllabus: [
        { week: 1, topic: 'Filosofi dan Rukun Akad', description: 'Memahami pentingnya akad dalam muamalat Islam serta syarat dan rukun yang harus dipenuhi.' },
        { week: 2, topic: 'Akad Pendanaan (Funding)', description: 'Studi mendalam tentang akad Wadiah dan Mudharabah serta aplikasinya pada produk giro, tabungan, dan deposito syariah.' },
        { week: 3, topic: 'Akad Pembiayaan (Financing)', description: 'Analisis akad Murabahah, Ijarah, Musyarakah, dan implementasinya pada produk pembiayaan modal kerja, investasi, dan konsumtif.' },
    ],
    modules: [
      { id: 'm1', title: 'Filosofi Akad dalam Islam', type: 'pdf', description: 'Materi ini membahas filosofi, rukun, dan syarat-syarat sahnya sebuah akad dalam perspektif hukum Islam, sebagai landasan untuk semua transaksi.', resourceUrl: '#' },
      { id: 'm2', title: 'Akad-akad Tabarru\' dan Tijarah', type: 'video', duration: '75min', description: 'Perbedaan mendasar antara akad sosial (non-profit) seperti qardh dan wakalah, dengan akad komersial (profit-oriented) seperti murabahah dan ijarah.', resourceUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4' },
      { id: 'm3', title: 'Analisis Produk Funding & Financing', type: 'video', duration: '80min', description: 'Video ini membedah skema produk-produk utama di bank syariah, dari sisi penghimpunan dana (funding) dan penyaluran dana (financing).', resourceUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/VolkswagenGTIReview.mp4' },
    ]
  },
  {
    id: 'AD501',
    title: 'Sejarah Peradaban Islam',
    instructor: 'Prof. Dr. Tariq An-Nawawi',
    instructorAvatarUrl: 'https://picsum.photos/seed/tariq/100/100',
    instructorBioKey: 'bio_tariq_annawawi',
    facultyId: 'adab',
    majorId: 'spi',
    sks: 3,
    description: 'Menelusuri jejak kegemilangan peradaban Islam dari masa Khulafaur Rasyidin, Bani Umayyah, Abbasiyah, hingga Andalusia, serta kontribusinya bagi dunia.',
    imageUrl: 'https://picsum.photos/seed/sejarah/600/400',
    progress: 100,
    gradeLetter: 'A',
    gradeNumeric: 98,
    completionDate: '2024-05-20',
    mode: 'VOD',
    status: 'Archived',
    learningObjectives: [
        'Mendeskripsikan periodisasi sejarah peradaban Islam.',
        'Menganalisis faktor-faktor kemajuan dan kemunduran peradaban Islam di berbagai era.',
        'Mengidentifikasi kontribusi ilmuwan Muslim dalam berbagai bidang ilmu pengetahuan.',
        'Mengambil ibrah (pelajaran) dari sejarah untuk konteks kekinian.'
    ],
    syllabus: [
        { week: 1, topic: 'Era Khulafaur Rasyidin', description: 'Kajian tentang model kepemimpinan, ekspansi wilayah, dan peletakan dasar-dasar administrasi negara Islam.' },
        { week: 2, topic: 'Dinasti Umayyah dan Abbasiyah', description: 'Perbandingan sistem pemerintahan, perkembangan ilmu pengetahuan, dan pusat-pusat peradaban di Damaskus dan Baghdad.' },
        { week: 3, topic: 'Keemasan Islam di Andalusia', description: 'Menelusuri jejak kemajuan sains, seni, dan arsitektur di Cordoba serta interaksi antar peradaban di Semenanjung Iberia.' },
    ],
    modules: [
      { id: 'm1', title: 'Era Kenabian dan Khulafaur Rasyidin', type: 'video', duration: '60min', description: 'Pembahasan mengenai periode fondasi peradaban Islam, mulai dari era kenabian di Madinah hingga masa kepemimpinan empat khalifah pertama.', resourceUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/WeAreGoingOnBullrun.mp4' },
      { id: 'm2', title: 'Puncak Keemasan di Baghdad', type: 'pdf', description: 'Ringkasan sejarah Dinasti Abbasiyah, fokus pada perkembangan ilmu pengetahuan di Baitul Hikmah, Baghdad, serta kontribusi para ilmuwan pada masa itu.', resourceUrl: '#' },
      { id: 'm3', title: 'Sains dan Filsafat di Andalusia', type: 'video', duration: '65min', description: 'Menelusuri jejak kemajuan ilmu pengetahuan, seni, dan filsafat di Cordoba, Spanyol, serta bagaimana interaksi antar peradaban terjadi.', resourceUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/WhatCarCanYouGetForAGrand.mp4' },
    ]
  },
  {
    id: 'PS601',
    title: 'Pengantar Psikologi Islam',
    instructor: 'Dr. Hana Al-Ghazali, M.Psi.',
    instructorAvatarUrl: 'https://picsum.photos/seed/hana/100/100',
    instructorBioKey: 'bio_hana_alghazali',
    facultyId: 'psikologi',
    majorId: 'psikologi-islam',
    sks: 2,
    description: 'Mengintegrasikan konsep-konsep psikologi modern dengan pandangan Islam tentang jiwa (nafs), hati (qalb), dan akal, serta metode tazkiyatun nafs.',
    imageUrl: 'https://picsum.photos/seed/psikologi/600/400',
    progress: 5,
    mode: 'VOD',
    status: 'Published',
    learningObjectives: [
        'Memahami konsep manusia (insan) dalam perspektif Al-Qur\'an dan Sunnah.',
        'Membedakan struktur kepribadian Islam (nafs, qalb, aql, ruh).',
        'Mengenal konsep kesehatan mental dan metode terapetik dalam Islam.',
        'Menganalisis fenomena psikologis modern dari kacamata Islam.'
    ],
    syllabus: [
        { week: 1, topic: 'Konsep Manusia dalam Psikologi Islam', description: 'Analisis terminologi kunci: Nafs, Qalb, Aql, dan Ruh serta hubungannya dengan perilaku manusia.' },
        { week: 2, topic: 'Kesehatan dan Gangguan Mental Perspektif Islam', description: 'Membahas konsep tazkiyatun nafs (penyucian jiwa) sebagai fondasi kesehatan mental dan pendekatan spiritual terhadap gangguan seperti waswas dan kesedihan.' },
    ],
    modules: [
      { id: 'm1', title: 'Konsep Manusia dalam Al-Qur\'an', type: 'video', duration: '55min', description: 'Analisis terminologi kunci dalam Al-Qur\'an yang berkaitan dengan jiwa manusia, seperti Nafs, Qalb, Aql, dan Ruh.', resourceUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4', captionsUrl: 'https://gist.githubusercontent.com/samdutton/ca37f3adaf4e23679957b8083e061177/raw/e19399addb3b8b548c7c71f085185a06065b7a39/sintel-en.vtt' },
      { id: 'm2', title: 'Teori Kepribadian Islam', type: 'pdf', description: 'Pembahasan tentang struktur kepribadian dalam Psikologi Islam yang meliputi konsep Nafs (jiwa), Qalb (hati), Aql (akal), dan Ruh, serta interaksi dinamis di antara keempatnya.', resourceUrl: '#' },
    ]
  },
  {
    id: 'SN701',
    title: 'AI & Etika Digital Islami',
    instructor: 'Dr. Eng. Faiz Rabbani',
    instructorAvatarUrl: 'https://picsum.photos/seed/faiz/100/100',
    instructorBioKey: 'bio_faiz_rabbani',
    facultyId: 'sains',
    majorId: 'ti-islami',
    sks: 3,
    description: 'Membahas penerapan Kecerdasan Buatan (AI) dalam aplikasi Islami (seperti deteksi tajwid, chatbot fatwa) serta meninjaunya dari sudut pandang etika dan maqashid syariah.',
    imageUrl: 'https://picsum.photos/seed/ai-islam/600/400',
    progress: 15,
    mode: 'Live',
    status: 'Published',
    learningObjectives: [
        'Memahami dasar-dasar teknologi Kecerdasan Buatan (AI).',
        'Menganalisis potensi dan tantangan penerapan AI dalam konteks keislaman.',
        'Merumuskan panduan etis (digital ethics) berdasarkan Maqashid Syariah.',
        'Mengevaluasi aplikasi-aplikasi Islami berbasis AI yang ada saat ini.'
    ],
    syllabus: [
        { week: 1, topic: 'Dasar-dasar AI dan Machine Learning', description: 'Pengenalan konsep-konsep inti AI seperti supervised/unsupervised learning, neural networks, dan natural language processing (NLP).' },
        { week: 2, topic: 'Maqashid Syariah sebagai Landasan Etika AI', description: 'Menerapkan lima tujuan utama syariat (hifdz ad-din, an-nafs, al-aql, an-nasl, al-mal) dalam merancang dan mengevaluasi teknologi AI.' },
    ],
    modules: [
      { id: 'm1', title: 'Dasar-dasar Machine Learning', type: 'video', duration: '70min', description: 'Pengenalan konsep-konsep inti Kecerdasan Buatan seperti supervised/unsupervised learning, neural networks, dan natural language processing (NLP).', resourceUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4' },
      { id: 'm2', title: 'Maqashid Syariah dalam Teknologi', type: 'pdf', description: 'Kajian tentang bagaimana lima tujuan utama syariat (hifdz ad-din, an-nafs, al-aql, an-nasl, al-mal) dapat dijadikan sebagai kerangka kerja etis dalam merancang dan mengevaluasi teknologi kecerdasan buatan.', resourceUrl: '#' },
      { 
        id: 'm_live_sn701', 
        title: 'Sesi Live: Etika & Fiqh Digital', 
        type: 'live', 
        description: 'Diskusi interaktif dan Q&A seputar tantangan fiqh dalam era digital dan kecerdasan buatan.',
        startTime: getFutureDate(5, 14, 0),
        liveUrl: 'https://meet.google.com/abc-defg-hij'
      }
    ]
  }
];

const newAssignments: Assignment[] = [];
initialCourses.forEach(course => {
    const assessmentModules = course.modules.filter(m => m.type === 'quiz' || m.type === 'hafalan');
    assessmentModules.forEach(mod => {
// FIX: Added missing 'category' property to the Assignment object.
        newAssignments.push({
            id: `ASG_${mod.id}`,
            courseId: course.id,
            title: mod.title,
            description: mod.description || 'Selesaikan tugas berikut sesuai instruksi.',
            dueDate: getFutureDate(14, 23, 59), // Due in 14 days
            files: [],
            submissions: [],
            type: mod.type === 'hafalan' ? 'hafalan' : 'file',
            category: mod.type === 'quiz' ? 'Ujian' : 'Tugas',
        });
    });
    // Filter out the assessment modules from the course materials
    course.modules = course.modules.filter(m => m.type !== 'quiz' && m.type !== 'hafalan');
});

export const COURSES_DATA: Course[] = initialCourses;

export const ACADEMIC_CALENDAR_EVENTS: AcademicCalendarEvent[] = [
    {
        id: 'ACE001',
        titleKey: 'event_semester_start',
        startDate: getYearDate(2024, 9, 2),
        category: 'academic'
    },
    {
        id: 'ACE002',
        titleKey: 'event_mid_terms',
        startDate: getYearDate(2024, 10, 21),
        endDate: getYearDate(2024, 10, 25),
        category: 'exam'
    },
    {
        id: 'ACE003',
        titleKey: 'event_final_terms',
        startDate: getYearDate(2024, 12, 16),
        endDate: getYearDate(2024, 12, 20),
        category: 'exam'
    },
    {
        id: 'ACE004',
        titleKey: 'event_eid_al_fitr',
        startDate: getYearDate(2024, 4, 10),
        endDate: getYearDate(2024, 4, 11),
        category: 'holiday'
    },
    {
        id: 'ACE005',
        titleKey: 'event_eid_al_adha',
        startDate: getYearDate(2024, 6, 17),
        category: 'holiday'
    },
    {
        id: 'ACE006',
        titleKey: 'event_new_year',
        startDate: getYearDate(2024, 7, 7),
        category: 'holiday'
    },
    {
        id: 'ACE007',
        titleKey: 'event_registration',
        startDate: getYearDate(2024, 8, 19),
        endDate: getYearDate(2024, 8, 30),
        category: 'registration'
    }
];

export const ASSIGNMENTS: Assignment[] = [
  {
    id: 'ASG001',
    courseId: 'AQ101',
    title: 'Esai Reflektif Pilar Keimanan',
    description: 'Tulis esai 2 halaman yang merefleksikan pemahaman Anda tentang salah satu dari enam pilar keimanan. Gunakan minimal 3 referensi dari Al-Qur\'an atau Hadis Shahih. Format file: PDF.',
    dueDate: getFutureDate(7, 23, 59), // Due in 7 days
    files: [
      { name: 'Panduan Penulisan Esai.pdf', url: '#' }
    ],
    submissions: [
        {
            studentId: MAHASISWA_USER.studentId,
            submittedAt: getFutureDate(-1, 10, 30), // Submitted yesterday
            file: { name: 'Esai_Pilar_Keimanan_Ahmad_Faris.pdf', url: '#' }
        }
    ],
    type: 'file',
    category: 'Tugas',
  },
  {
    id: 'ASG_HFL01',
    courseId: 'AQ101',
    title: 'Setoran Hafalan: Tiga Landasan Utama',
    description: 'Hafalkan matan Tiga Landasan Utama (Al-Ushul Ats-Tsalatsah) karya Syaikh Muhammad bin Abdul Wahhab. Rekam setoran Anda dengan pelafalan yang jelas dan tartil.',
    dueDate: getFutureDate(10, 23, 59),
    files: [],
    submissions: [],
    type: 'hafalan',
    category: 'Tugas',
  },
  {
    id: 'ASG002',
    courseId: 'FQ201',
    title: 'Analisis Studi Kasus Riba',
    description: 'Analisis studi kasus terlampir mengenai transaksi di lembaga keuangan. Identifikasi potensi riba, gharar, atau maysir dan berikan solusi syar\'i alternatif.',
    dueDate: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000).toISOString(), // Overdue by 2 days
    files: [
      { name: 'Studi Kasus Transaksi Keuangan.pdf', url: '#' }
    ],
    submissions: [],
    type: 'file',
    category: 'Tugas',
  },
  {
    id: 'ASG003',
    courseId: 'AD501',
    title: 'Presentasi Kontribusi Ilmuwan Muslim',
    description: 'Buat presentasi 10 slide mengenai kontribusi salah satu ilmuwan Muslim (pilih dari daftar yang disediakan) pada peradaban dunia. Kumpulkan dalam format PPTX atau PDF.',
    dueDate: getFutureDate(-20, 23, 59), // Due 20 days ago
    files: [
      { name: 'Daftar Ilmuwan Muslim.pdf', url: '#' }
    ],
    submissions: [
      {
        studentId: 'UC2024001',
        submittedAt: getFutureDate(-21, 14, 30),
        file: { name: 'Presentasi_Ibn_Al-Haytham_Ahmad_Faris.pptx', url: '#' },
        gradeLetter: 'A',
        gradeNumeric: 95,
        feedback: 'Kerja yang sangat baik, Ahmad! Analisis Anda tentang kontribusi Ibn Al-Haytham dalam bidang optik sangat mendalam dan presentasinya visualnya menarik. Pertahankan!'
      }
    ],
    type: 'file',
    category: 'Tugas',
  },
  {
    id: 'ASG004',
    courseId: 'TR401',
    title: 'Rancangan RPP Inovatif',
    description: 'Rancang satu Rencana Pelaksanaan Pembelajaran (RPP) untuk materi PAI tingkat SMA dengan mengintegrasikan teknologi atau model pembelajaran aktif.',
    dueDate: getFutureDate(15, 23, 59), // Due in 15 days
    files: [],
    submissions: [
      {
        studentId: 'UC2024001',
        submittedAt: getFutureDate(-2, 18, 0), // Submitted 2 days ago
        file: { name: 'RPP_PAI_Draft_1.pdf', url: '#' },
        gradeLetter: 'B-',
        gradeNumeric: 81,
        feedback: 'Konsepnya sudah bagus, namun mohon perjelas lagi bagian asesmen formatifnya. Pastikan terukur dan relevan dengan tujuan pembelajaran. Silakan direvisi.'
      },
      {
        studentId: 'UC2024001',
        submittedAt: new Date().toISOString(), // Submitted today
        file: { name: 'RPP_PAI_Blended_Learning_Ahmad_Faris_REV1.pdf', url: '#' },
      }
    ],
    type: 'file',
    category: 'Tugas',
  },
   {
    id: 'ASG005',
    courseId: 'HD202',
    title: 'Kritik Sanad Hadis',
    description: 'Pilih satu hadis dari lampiran dan lakukan kritik sanad dasar berdasarkan metodologi yang telah dipelajari.',
    dueDate: getFutureDate(5, 23, 59), // Same day as a live class
    files: [{name: 'Kumpulan_Hadis.pdf', url: '#'}],
    submissions: [],
    type: 'file',
    category: 'Tugas',
  },
   {
    id: 'ASG_HFL02',
    courseId: 'HD202',
    title: 'Setoran Hafalan: Hadits Pertama Arba\'in',
    description: 'Hafalkan matan dan sanad hadits pertama dari kitab Arba\'in An-Nawawi tentang niat. Pastikan makhraj dan harakat diucapkan dengan benar.',
    dueDate: getFutureDate(12, 23, 59),
    files: [],
    submissions: [],
    type: 'hafalan',
    category: 'Tugas',
  },
  ...newAssignments,
  {
    id: 'ASG_NEW_1',
    courseId: 'EK305',
    title: 'Analisis Produk Bank Syariah',
    description: 'Pilih satu produk pembiayaan dari bank syariah di Indonesia. Analisis akad yang digunakan, skema, serta potensi risikonya. Buat laporan 3 halaman.',
    dueDate: getFutureDate(20, 23, 59),
    files: [],
    submissions: [],
    type: 'file',
    category: 'Tugas',
  },
  {
    id: 'ASG_NEW_2',
    courseId: 'SN701',
    title: 'Proyek Akhir: Proposal Aplikasi Islami berbasis AI',
    description: 'Buat proposal (5-7 halaman) untuk sebuah aplikasi Islami yang memanfaatkan teknologi AI. Jelaskan masalah yang ingin diselesaikan, teknologi AI yang akan digunakan, dan pertimbangan etika syariahnya.',
    dueDate: getFutureDate(45, 23, 59),
    files: [{name: 'Template_Proposal_Proyek.docx', url: '#'}],
    submissions: [],
    type: 'file',
    category: 'Ujian',
  }
];

export const INITIAL_ELIBRARY_RESOURCES: LibraryResource[] = [
    {
        id: 'lib001',
        title: 'Fiqh Al-Muamalat Al-Maliyah Al-Muashirah',
        author: 'Prof. Dr. Wahbah Az-Zuhaili',
        year: 2002,
        type: 'book',
        description: 'Buku komprehensif yang membahas transaksi keuangan kontemporer dari perspektif fiqh, mencakup perbankan, asuransi, dan pasar modal.',
        coverUrl: 'https://picsum.photos/seed/fiqh-book/300/400',
        sourceType: 'link',
        sourceUrl: '#',
    },
    {
        id: 'lib002',
        title: 'Tafsir Al-Mishbah',
        author: 'Prof. Dr. M. Quraish Shihab',
        year: 2002,
        type: 'book',
        description: 'Tafsir Al-Qur\'an lengkap 30 juz dengan pendekatan tematik dan kontekstual yang relevan dengan kondisi masyarakat Indonesia.',
        coverUrl: 'https://picsum.photos/seed/tafsir-book/300/400',
        sourceType: 'link',
        sourceUrl: '#',
    },
    {
        id: 'lib003',
        title: 'Ar-Rahiq Al-Makhtum',
        author: 'Syaikh Shafiyyurrahman Al-Mubarakfuri',
        year: 1976,
        type: 'book',
        description: 'Karya sirah Nabawiyah (biografi Nabi Muhammad ﷺ) yang memenangkan penghargaan internasional, disajikan secara kronologis dan detail.',
        coverUrl: 'https://picsum.photos/seed/sirah-book/300/400',
        sourceType: 'link',
        sourceUrl: '#',
    },
    {
        id: 'lib004',
        title: 'Journal of Islamic Economic Studies',
        author: 'Islamic Research and Training Institute (IRTI)',
        year: 2023,
        type: 'journal',
        description: 'Jurnal ilmiah yang memuat penelitian terbaru tentang ekonomi, keuangan, dan perbankan Islam dari para akademisi di seluruh dunia.',
        coverUrl: 'https://picsum.photos/seed/journal-eco/300/400',
        sourceType: 'link',
        sourceUrl: '#',
    },
    {
        id: 'lib005',
        title: 'Minhaj Al-Muslim',
        author: 'Syaikh Abu Bakar Jabir Al-Jaza\'iri',
        year: 1964,
        type: 'book',
        description: 'Panduan lengkap bagi setiap Muslim yang mencakup aqidah, ibadah, adab, akhlak, dan muamalat berdasarkan Al-Qur\'an dan Sunnah.',
        coverUrl: 'https://picsum.photos/seed/minhaj-book/300/400',
        sourceType: 'link',
        sourceUrl: '#',
    },
    {
        id: 'lib006',
        title: 'The Role of Maqasid al-Sharia in Islamic Finance',
        author: 'Dr. Asyraf Wajdi Dusuki',
        year: 2011,
        type: 'journal',
        description: 'Artikel jurnal yang menganalisis pentingnya Maqashid Syariah (tujuan-tujuan syariat) sebagai landasan filosofis dalam pengembangan produk keuangan syariah.',
        coverUrl: 'https://picsum.photos/seed/journal-maqasid/300/400',
        sourceType: 'link',
        sourceUrl: '#',
    }
];

export const DISCUSSION_THREADS: DiscussionThread[] = [
    {
        id: 'DT001',
        courseId: 'AQ101',
        title: 'Pertanyaan tentang Batasan Sifat Istiwa',
        authorId: MAHASISWA_USER.studentId,
        createdAt: getPastDate(2),
        isPinned: true,
        isClosed: false,
        posts: [
            { id: 'P001', authorId: MAHASISWA_USER.studentId, createdAt: getPastDate(2), content: 'Assalamu\'alaikum, Ustadz. Saya masih bingung bagaimana kita harus meyakini sifat Istiwa Allah tanpa terjerumus ke dalam tasybih (menyerupakan dengan makhluk). Mohon penjelasannya, jazakallah.' },
            { id: 'P002', authorId: DOSEN_USER.studentId, createdAt: getPastDate(1), content: 'Wa\'alaikumussalam. Pertanyaan yang bagus. Ahlussunnah meyakini Allah ber-istiwa di atas \'Arsy sesuai dengan keagungan-Nya, tanpa menanyakan "bagaimana" (bila kaif), tanpa menyerupakan dengan makhluk, tanpa menolak, dan tanpa mengubah maknanya. Kita tetapkan sesuai yang Allah kabarkan dalam Al-Qur\'an.' },
        ],
    },
    {
        id: 'DT002',
        courseId: 'AQ101',
        title: 'Dalil-dalil Tauhid Uluhiyah',
        authorId: 'UC2024002', // Another student
        createdAt: getPastDate(5),
        isPinned: false,
        isClosed: false,
        posts: [
            { id: 'P003', authorId: 'UC2024002', createdAt: getPastDate(5), content: 'Apakah ada yang bisa berbagi dalil-dalil paling kuat dari Al-Qur\'an tentang Tauhid Uluhiyah selain surat Al-Ikhlas?' },
            { id: 'P008', authorId: MAHASISWA_USER.studentId, createdAt: getPastDate(4), content: 'Selain Al-Ikhlas, ayat kursi (Al-Baqarah: 255) adalah ayat yang sangat agung yang menjelaskan tentang keesaan dan kekuasaan Allah. Juga awal surat Al-Hadid banyak menjelaskan tentang Asma wa Sifat.' }
        ]
    },
    {
        id: 'DT003',
        courseId: 'FQ201',
        title: 'Diskusi: Hukum Dropshipping',
        authorId: MAHASISWA_USER.studentId,
        createdAt: getPastDate(10),
        isPinned: false,
        isClosed: true,
        posts: [
             { id: 'P004', authorId: MAHASISWA_USER.studentId, createdAt: getPastDate(10), content: 'Apakah skema dropshipping diperbolehkan? Karena penjual menjual barang yang belum ia miliki.' },
             { id: 'P005', authorId: PRODI_ADMIN_USER.studentId, createdAt: getPastDate(9), content: 'Ini masuk dalam pembahasan menjual apa yang tidak dimiliki. Ulama berbeda pendapat. Sebagian membolehkan jika skemanya diubah menjadi akad salam atau wakalah (perwakilan). Diskusi yang menarik. Thread ini saya tutup ya, akan dibahas lebih lanjut di sesi live pekan depan.' },
        ],
    },
     {
        id: 'DT004',
        courseId: 'FQ201',
        title: 'Perbedaan Murabahah dan Musyarakah Mutanaqisah?',
        authorId: 'UC2024003', // another student
        createdAt: getPastDate(1),
        isPinned: false,
        isClosed: false,
        posts: [
            { id: 'P006', authorId: 'UC2024003', createdAt: getPastDate(1), content: 'Assalamu\'alaikum. Saya masih belum paham betul perbedaan mendasar antara pembiayaan KPR dengan akad Murabahah dan MMQ. Keduanya kan sama-sama untuk kepemilikan rumah. Mohon pencerahannya.' },
            { id: 'P007', authorId: MAHASISWA_USER.studentId, createdAt: getPastDate(0.5), content: 'Wa\'alaikumussalam. Setahu saya, kalau Murabahah itu jual-beli dengan margin keuntungan yang disepakati di awal, jadi cicilannya flat. Kalau MMQ itu kemitraan, porsi kepemilikan bank berkurang seiring kita mencicil, jadi ada bagi hasil dari sewa juga. Mungkin ustadzah bisa koreksi.' }
        ],
    },
    {
        id: 'DT005',
        courseId: 'SN701',
        title: 'Potensi Bias pada Chatbot Fatwa Berbasis AI',
        authorId: 'UC2024002', // Siti Maryam
        createdAt: getPastDate(3),
        isPinned: true,
        isClosed: false,
        posts: [
            { id: 'P009', authorId: 'UC2024002', createdAt: getPastDate(3), content: 'Assalamu\'alaikum, Ustadz. Saya tertarik dengan konsep chatbot fatwa, tapi khawatir, bagaimana kita memastikan AI tidak memberikan jawaban yang bias atau salah, terutama jika data training-nya terbatas pada satu mazhab saja?' },
            { id: 'P010', authorId: 'DSN202105', createdAt: getPastDate(2.5), content: 'Wa\'alaikumussalam. Pertanyaan kritis, Siti. Ini adalah tantangan utama dalam etika AI Islami. Untuk mitigasi bias, pertama, sumber data harus komprehensif dan merepresentasikan berbagai pandangan ulama mu\'tabar. Kedua, transparansi model AI sangat penting; kita harus tahu *mengapa* AI memberikan jawaban tertentu. Ketiga, harus selalu ada mekanisme supervisi oleh dewan syariah manusia. AI di sini berperan sebagai asisten, bukan mufti independen.' },
            { id: 'P011', authorId: MAHASISWA_USER.studentId, createdAt: getPastDate(1), content: 'Terima kasih atas penjelasannya, Ustadz. Berarti peran manusia sebagai verifikator akhir tetap tidak tergantikan ya.'}
        ],
    },
    {
        id: 'DT006',
        courseId: 'SN701',
        title: 'Halal-chain: Penerapan Blockchain untuk Industri Halal',
        authorId: MAHASISWA_USER.studentId,
        createdAt: getPastDate(7),
        isPinned: false,
        isClosed: false,
        posts: [
            { id: 'P012', authorId: MAHASISWA_USER.studentId, createdAt: getPastDate(7), content: 'Saya baca tentang konsep "halal-chain" yang menggunakan blockchain untuk menjamin kehalalan produk dari hulu ke hilir. Apakah ini sudah banyak diterapkan di Indonesia dan apa tantangan terbesarnya?' },
            { id: 'P013', authorId: 'DSN202105', createdAt: getPastDate(6), content: 'Betul sekali, konsepnya sangat menjanjikan. Di Indonesia sudah ada beberapa startup yang merintis, tapi tantangannya masih besar. Terutama pada adopsi teknologi di seluruh rantai pasok (supply chain), standardisasi data, dan biaya implementasi awal. Namun potensinya untuk meningkatkan kepercayaan konsumen sangat besar.' },
        ],
    }
];

export const ANNOUNCEMENTS_DATA: Announcement[] = [
  {
    id: 'AN001',
    title: 'Perubahan Jadwal Ujian Tengah Semester',
    content: 'Assalamu\'alaikum Warahmatullahi Wabarakatuh.\n\nDiberitahukan kepada seluruh mahasiswa bahwa jadwal Ujian Tengah Semester (UTS) untuk beberapa mata kuliah mengalami perubahan. Mohon untuk memeriksa jadwal terbaru di kalender akademik Anda.\n\nPerubahan ini dilakukan untuk mengakomodasi jadwal dosen dan memastikan kelancaran pelaksanaan ujian. Terima kasih atas perhatiannya.\n\nWassalamu\'alaikum Warahmatullahi Wabarakatuh.',
    authorName: 'Dr. Aisyah Hasanah',
    timestamp: getPastDate(3),
    category: 'Akademik',
  },
  {
    id: 'AN002',
    title: 'Pelatihan Penggunaan E-Library Terbaru',
    content: 'Dalam rangka meningkatkan literasi digital, kami akan mengadakan sesi pelatihan online tentang cara efektif menggunakan fitur-fitur terbaru di E-Library UlumCampus. Sesi akan diadakan pada hari Sabtu pekan ini pukul 10:00 WIB. Link Zoom akan dibagikan melalui email.',
    authorName: 'Admin Akademik',
    timestamp: getPastDate(5),
    category: 'Akademik',
  },
  {
    id: 'AN003',
    title: 'Pembaruan Sistem & Maintenance',
    content: 'Akan dilakukan pemeliharaan sistem pada hari Ahad dini hari, mulai pukul 01:00 hingga 04:00 WIB. Selama periode tersebut, akses ke platform UlumCampus mungkin akan terganggu. Mohon maaf atas ketidaknyamanannya.',
    authorName: 'Tim IT UlumCampus',
    timestamp: getPastDate(8),
    category: 'Kampus',
  }
];


export const NOTIFICATIONS_DATA: Notification[] = [
  {
    id: 'N001',
    type: 'forum',
    messageKey: 'notification_forum_reply',
    context: 'Dr. Yusuf Al-Fatih',
    timestamp: getPastDate(0.2), // a few hours ago
    isRead: false,
    link: { page: 'course-detail', params: { courseId: 'AQ101', initialTab: 'discussion', threadId: 'DT001' } },
  },
  {
    id: 'N002',
    type: 'grade',
    messageKey: 'notification_grade_update',
    context: 'Presentasi Kontribusi Ilmuwan Muslim',
    timestamp: getPastDate(1),
    isRead: false,
    link: { page: 'assignments', params: { assignmentId: 'ASG003' } },
  },
  {
    id: 'N003',
    type: 'assignment',
    messageKey: 'notification_new_assignment',
    context: 'Analisis Produk Bank Syariah',
    timestamp: getPastDate(2),
    isRead: true,
    link: { page: 'assignments', params: { assignmentId: 'ASG_NEW_1' } },
  },
  {
    id: 'N004',
    type: 'announcement',
    messageKey: 'notification_announcement',
    context: 'Dr. Aisyah Hasanah',
    timestamp: getPastDate(3),
    isRead: true,
    link: { page: 'announcements', params: { announcementId: 'AN001' } },
  },
];

export interface PaymentItem {
  id: string;
  titleKey: string;
  descriptionKey: string;
  amount: number;
  status: 'paid' | 'unpaid' | 'pending';
  dueDate?: string;
}

export interface PaymentHistoryItem {
  id: string;
  title: string;
  amount: number;
  date: string;
  status: 'completed' | 'failed' | 'pending';
  paymentMethod?: string;
}

export const PAYMENT_ITEMS_MOCK: PaymentItem[] = [
  {
    id: 'registration',
    titleKey: 'administrasi_registration_title',
    descriptionKey: 'administrasi_registration_desc',
    amount: 5000000,
    status: 'unpaid',
    dueDate: '2024-09-15',
  },
  {
    id: 'semester',
    titleKey: 'administrasi_semester_title',
    descriptionKey: 'administrasi_semester_desc',
    amount: 3500000,
    status: 'unpaid',
    dueDate: '2024-09-30',
  },
  {
    id: 'exam',
    titleKey: 'administrasi_exam_title',
    descriptionKey: 'administrasi_exam_desc',
    amount: 250000,
    status: 'paid',
  },
];

export const PAYMENT_HISTORY_MOCK: PaymentHistoryItem[] = [
  {
    id: '1',
    title: 'Pembayaran Semester',
    amount: 3500000,
    date: '2024-08-15',
    status: 'completed',
  },
  {
    id: '2',
    title: 'Pembayaran Registrasi',
    amount: 5000000,
    date: '2024-07-20',
    status: 'completed',
  },
];

export interface PaymentMethod {
  id: string;
  nameKey: string;  // Translation key instead of translated string
  icon: string;
}

export const PAYMENT_METHODS: PaymentMethod[] = [
  { 
    id: 'bank_transfer', 
    nameKey: 'administrasi_payment_method_bank_transfer', 
    icon: (
      <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
        <path strokeLinecap="round" strokeLinejoin="round" d="M3 21l18 0M12 3v18m-9-9l9-9 9 9" />
      </svg>
    ) 
  },
  { 
    id: 'credit_card', 
    nameKey: 'administrasi_payment_method_credit_card', 
    icon: (
      <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
        <path strokeLinecap="round" strokeLinejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
      </svg>
    ) 
  },
  { 
    id: 'e_wallet', 
    nameKey: 'administrasi_payment_method_e_wallet', 
    icon: (
      <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
        <path strokeLinecap="round" strokeLinejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
      </svg>
    ) 
  },
  { 
    id: 'virtual_account', 
    nameKey: 'administrasi_payment_method_virtual_account', 
    icon: (
      <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
        <path strokeLinecap="round" strokeLinejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
      </svg>
    ) 
  },
];
