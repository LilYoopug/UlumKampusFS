import { 
  User, 
  Course, 
  Assignment, 
  Announcement, 
  LibraryResource, 
  DiscussionThread, 
  Notification, 
 Grade, 
  CalendarEvent, 
  Faculty, 
  Major,
  CourseModule
} from '../types';
import { 
  ALL_USERS,
  COURSES_DATA,
  ASSIGNMENTS,
  ANNOUNCEMENTS_DATA,
  INITIAL_ELIBRARY_RESOURCES,
  DISCUSSION_THREADS,
  NOTIFICATIONS_DATA,
  ACADEMIC_CALENDAR_EVENTS,
  FACULTIES
} from '../constants';

// Simulate network delay
const delay = (ms: number) => new Promise(resolve => setTimeout(resolve, ms));

// Mock API responses
export const mockAuthAPI = {
  login: async (email: string, password: string) => {
    await delay(500);
    const user = ALL_USERS.find(u => u.email === email);
    if (!user) {
      throw new Error('Invalid credentials');
    }
    return {
      data: {
        token: 'mock-token-' + Date.now(),
        user: user
      }
    };
  },

  register: async (userData: { name: string; email: string; password: string }) => {
    await delay(500);
    const newUser: User = {
      ...userData,
      studentId: 'UC' + Date.now().toString().slice(-5),
      avatarUrl: 'https://picsum.photos/seed/' + userData.name.split(' ')[0].toLowerCase() + '/100/100',
      role: 'Mahasiswa',
      joinDate: new Date().toISOString().split('T')[0],
      bio: '',
      studentStatus: 'Aktif',
      gpa: 0,
      totalSks: 0,
      facultyId: undefined,
      majorId: undefined
    };
    return {
      data: {
        token: 'mock-token-' + Date.now(),
        user: newUser
      }
    };
  },

  logout: async () => {
    await delay(300);
    return { data: { message: 'Logged out successfully' } };
  },

  forgotPassword: async (email: string) => {
    await delay(300);
    return { data: { message: 'Password reset link sent to your email' } };
  },

  resetPassword: async (token: string, email: string, password_confirmation: string) => {
    await delay(300);
    return { data: { message: 'Password reset successfully' } };
  },

  getProfile: async () => {
    await delay(300);
    return { data: ALL_USERS[0] };
  },

  getCurrentUser: async () => {
    await delay(300);
    return { data: ALL_USERS[0] };
  }
};

export const mockUserAPI = {
  getAll: async () => {
    await delay(300);
    return { data: ALL_USERS };
  },

  getById: async (id: string) => {
    await delay(300);
    const user = ALL_USERS.find(u => u.studentId === id);
    if (!user) throw new Error('User not found');
    return { data: user };
  },

  create: async (userData: Partial<User>) => {
    await delay(500);
    const newUser: User = {
      ...userData,
      studentId: 'UC' + Date.now().toString().slice(-5),
      avatarUrl: 'https://picsum.photos/seed/' + (userData.name || 'user').split(' ')[0].toLowerCase() + '/100/100',
      role: userData.role || 'Mahasiswa',
      joinDate: new Date().toISOString().split('T')[0],
      bio: userData.bio || '',
      studentStatus: userData.studentStatus || 'Aktif',
      gpa: userData.gpa || 0,
      totalSks: userData.totalSks || 0
    };
    return { data: newUser };
  },

  update: async (id: string, userData: Partial<User>) => {
    await delay(500);
    const user = ALL_USERS.find(u => u.studentId === id);
    if (!user) throw new Error('User not found');
    const updatedUser = { ...user, ...userData };
    return { data: updatedUser };
  },

  delete: async (id: string) => {
    await delay(300);
    return { data: undefined };
  }
};

export const mockCourseAPI = {
 getAll: async (params?: { facultyId?: string; status?: string; search?: string; majorId?: string }) => {
    await delay(300);
    let filteredCourses = [...COURSES_DATA];
    
    if (params?.facultyId) {
      filteredCourses = filteredCourses.filter(c => c.facultyId === params.facultyId);
    }
    if (params?.status) {
      filteredCourses = filteredCourses.filter(c => c.status === params.status);
    }
    if (params?.search) {
      filteredCourses = filteredCourses.filter(c => 
        c.title.toLowerCase().includes(params.search!.toLowerCase()) ||
        c.instructor.toLowerCase().includes(params.search!.toLowerCase())
      );
    }
    if (params?.majorId) {
      filteredCourses = filteredCourses.filter(c => c.majorId === params.majorId);
    }
    
    return { data: filteredCourses };
  },

  getPublic: async (params?: { search?: string }) => {
    await delay(300);
    let filteredCourses = COURSES_DATA.filter(c => c.status === 'Published');
    
    if (params?.search) {
      filteredCourses = filteredCourses.filter(c => 
        c.title.toLowerCase().includes(params.search!.toLowerCase()) ||
        c.instructor.toLowerCase().includes(params.search!.toLowerCase())
      );
    }
    
    return { data: filteredCourses };
  },

  getById: async (id: string) => {
    await delay(300);
    const course = COURSES_DATA.find(c => c.id === id);
    if (!course) throw new Error('Course not found');
    return { data: course };
  },

  create: async (courseData: Partial<Course>) => {
    await delay(500);
    const newCourse: Course = {
      ...courseData,
      id: 'COURSE' + Date.now().toString().slice(-4),
      progress: 0,
      mode: courseData.mode || 'VOD',
      status: courseData.status || 'Draft',
      learningObjectives: courseData.learningObjectives || [],
      syllabus: courseData.syllabus || [],
      modules: courseData.modules || []
    } as Course;
    return { data: newCourse };
  },

  update: async (id: string, courseData: Partial<Course>) => {
    await delay(500);
    const course = COURSES_DATA.find(c => c.id === id);
    if (!course) throw new Error('Course not found');
    const updatedCourse = { ...course, ...courseData } as Course;
    return { data: updatedCourse };
  },

  delete: async (id: string) => {
    await delay(300);
    return { data: undefined };
  }
};

export const mockAssignmentAPI = {
  getAll: async () => {
    await delay(300);
    return { data: ASSIGNMENTS };
  },

  getById: async (id: string) => {
    await delay(300);
    const assignment = ASSIGNMENTS.find(a => a.id === id);
    if (!assignment) throw new Error('Assignment not found');
    return { data: assignment };
  },

  create: async (assignmentData: Partial<Assignment>) => {
    await delay(500);
    const newAssignment: Assignment = {
      ...assignmentData,
      id: 'ASG' + Date.now().toString().slice(-4),
      files: assignmentData.files || [],
      submissions: assignmentData.submissions || [],
      type: assignmentData.type || 'file',
      category: assignmentData.category || 'Tugas'
    } as Assignment;
    return { data: newAssignment };
  },

  update: async (id: string, assignmentData: Partial<Assignment>) => {
    await delay(500);
    const assignment = ASSIGNMENTS.find(a => a.id === id);
    if (!assignment) throw new Error('Assignment not found');
    const updatedAssignment = { ...assignment, ...assignmentData } as Assignment;
    return { data: updatedAssignment };
  },

  delete: async (id: string) => {
    await delay(30);
    return { data: undefined };
  }
};

export const mockAnnouncementAPI = {
  getAll: async () => {
    await delay(300);
    return { data: { data: ANNOUNCEMENTS_DATA } };
  },

  getById: async (id: string) => {
    await delay(30);
    const announcement = ANNOUNCEMENTS_DATA.find(a => a.id === id);
    if (!announcement) throw new Error('Announcement not found');
    return { data: { data: announcement } };
  },

  create: async (announcementData: Partial<Announcement>) => {
    await delay(500);
    const newAnnouncement: Announcement = {
      ...announcementData,
      id: 'ANN' + Date.now().toString().slice(-4),
      authorName: announcementData.authorName || 'Admin',
      timestamp: new Date().toISOString(),
      category: announcementData.category || 'Akademik'
    } as Announcement;
    return { data: { data: newAnnouncement } };
  },

  update: async (id: string, announcementData: Partial<Announcement>) => {
    await delay(500);
    const announcement = ANNOUNCEMENTS_DATA.find(a => a.id === id);
    if (!announcement) throw new Error('Announcement not found');
    const updatedAnnouncement = { ...announcement, ...announcementData } as Announcement;
    return { data: { data: updatedAnnouncement } };
  },

  delete: async (id: string) => {
    await delay(300);
    return { data: undefined };
  }
};

export const mockLibraryResourceAPI = {
  getAll: async () => {
    await delay(300);
    return { data: INITIAL_ELIBRARY_RESOURCES };
  },

  getById: async (id: string) => {
    await delay(300);
    const resource = INITIAL_ELIBRARY_RESOURCES.find(r => r.id === id);
    if (!resource) throw new Error('Resource not found');
    return { data: resource };
  },

  create: async (resourceData: Partial<LibraryResource>) => {
    await delay(500);
    const newResource: LibraryResource = {
      ...resourceData,
      id: 'LIB' + Date.now().toString().slice(-4),
      year: resourceData.year || new Date().getFullYear(),
      type: resourceData.type || 'book',
      sourceType: resourceData.sourceType || 'link',
      coverUrl: resourceData.coverUrl || 'https://picsum.photos/seed/library/300/400'
    } as LibraryResource;
    return { data: newResource };
  },

  update: async (id: string, resourceData: Partial<LibraryResource>) => {
    await delay(500);
    const resource = INITIAL_ELIBRARY_RESOURCES.find(r => r.id === id);
    if (!resource) throw new Error('Resource not found');
    const updatedResource = { ...resource, ...resourceData } as LibraryResource;
    return { data: updatedResource };
  },

  delete: async (id: string) => {
    await delay(300);
    return { data: undefined };
  }
};

export const mockDiscussionThreadAPI = {
  getAll: async () => {
    await delay(300);
    return { data: DISCUSSION_THREADS };
  },

  getById: async (id: string) => {
    await delay(300);
    const thread = DISCUSSION_THREADS.find(t => t.id === id);
    if (!thread) throw new Error('Thread not found');
    return { data: thread };
  },

  create: async (threadData: Partial<DiscussionThread>) => {
    await delay(500);
    const newThread: DiscussionThread = {
      ...threadData,
      id: 'DT' + Date.now().toString().slice(-4),
      posts: threadData.posts || [],
      isPinned: false,
      isClosed: false,
      createdAt: new Date().toISOString()
    } as DiscussionThread;
    return { data: newThread };
  },

  update: async (id: string, threadData: Partial<DiscussionThread>) => {
    await delay(500);
    const thread = DISCUSSION_THREADS.find(t => t.id === id);
    if (!thread) throw new Error('Thread not found');
    const updatedThread = { ...thread, ...threadData } as DiscussionThread;
    return { data: updatedThread };
  },

  delete: async (id: string) => {
    await delay(300);
    return { data: undefined };
  }
};

export const mockNotificationAPI = {
  getAll: async () => {
     await delay(300);
     return { data: NOTIFICATIONS_DATA };
   },

   getById: async (id: string) => {
     await delay(300);
     const notification = NOTIFICATIONS_DATA.find(n => n.id === id);
     if (!notification) throw new Error('Notification not found');
     return { data: notification };
   },

   create: async (notificationData: Partial<Notification>) => {
     await delay(500);
     const newNotification: Notification = {
       ...notificationData,
       id: 'NOT' + Date.now().toString().slice(-4),
       timestamp: new Date().toISOString(),
       isRead: false
     } as Notification;
     return { data: newNotification };
   },

   update: async (id: string, notificationData: Partial<Notification>) => {
     await delay(500);
     const notification = NOTIFICATIONS_DATA.find(n => n.id === id);
     if (!notification) throw new Error('Notification not found');
     const updatedNotification = { ...notification, ...notificationData } as Notification;
     return { data: updatedNotification };
   },

   delete: async (id: string) => {
     await delay(300);
     return { data: undefined };
   },

   markAsRead: async (id: string) => {
     await delay(300);
     const notification = NOTIFICATIONS_DATA.find(n => n.id === id);
     if (!notification) throw new Error('Notification not found');
     return { data: { ...notification, isRead: true } };
   },

   markAllAsRead: async () => {
     await delay(300);
     return { data: { message: 'All notifications marked as read' } };
   }
 };

 export const mockPaymentAPI = {
   createTransaction: async (order_id: string, amount: number) => {
     await delay(500);
     return { 
       data: { 
         snap_token: 'mock-token-' + Date.now(),
         order_id,
         amount,
         status: 'pending'
       }
     };
   },

   checkTransactionStatus: async (order_id: string) => {
     await delay(300);
     return { 
       data: { 
         order_id,
         status: 'completed',
         transaction_time: new Date().toISOString()
       }
     };
   }
 };
