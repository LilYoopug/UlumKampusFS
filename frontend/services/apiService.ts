import axios, { AxiosResponse } from 'axios';
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

// Base API configuration
const API_BASE_URL = process.env.VITE_API_BASE_URL || 'http://localhost:8000/api';

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Request interceptor to add token to authenticated requests
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor to handle token expiration
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Clear token and redirect to login
      localStorage.removeItem('auth_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// Authentication API
export const authAPI = {
  login: (email: string, password: string): Promise<AxiosResponse<{ token: string; user: User }>> => {
    return api.post('/login', { email, password });
  },

  register: (userData: { name: string; email: string; phone_number: string; password: string; role?: string }): Promise<AxiosResponse<{ token: string; user: User }>> => {
    return api.post('/register', userData);
  },

  logout: (): Promise<AxiosResponse<{ message: string }>> => {
    return api.post('/logout');
  },

  forgotPassword: (email: string): Promise<AxiosResponse<{ message: string }>> => {
    return api.post('/forgot-password', { email });
  },

 resetPassword: (token: string, email: string, password: string, password_confirmation: string): Promise<AxiosResponse<{ message: string }>> => {
   return api.post('/reset-password', {
     token,
     email,
     password,
     password_confirmation
   });
 },

  getProfile: (): Promise<AxiosResponse<User>> => {
    return api.get('/profile');
  },

  getCurrentUser: (): Promise<AxiosResponse<User>> => {
    return api.get('/user');
  }
};

// User API
export const userAPI = {
  getAll: (): Promise<AxiosResponse<any>> => {
    return api.get('/users');
  },

  getById: (id: string): Promise<AxiosResponse<any>> => {
    return api.get(`/users/${id}`);
  },

  create: (userData: Partial<User>): Promise<AxiosResponse<any>> => {
    return api.post('/users', userData);
 },

  update: (id: string, userData: Partial<User>): Promise<AxiosResponse<any>> => {
    return api.put(`/users/${id}`, userData);
 },

  delete: (id: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/users/${id}`);
  }
};

// Course API
export const courseAPI = {
  getAll: (params?: { facultyId?: string; status?: string; search?: string; majorId?: string }): Promise<AxiosResponse<any>> => {
    return api.get('/courses', { params });
  },

  getPublic: (params?: { search?: string }): Promise<AxiosResponse<any>> => {
    return api.get('/public/courses', { params });
  },

  getById: (id: string): Promise<AxiosResponse<Course>> => {
    return api.get(`/courses/${id}`);
  },

  create: (courseData: Partial<Course>): Promise<AxiosResponse<Course>> => {
    return api.post('/courses', courseData);
  },

  update: (id: string, courseData: Partial<Course>): Promise<AxiosResponse<Course>> => {
    return api.put(`/courses/${id}`, courseData);
  },

  delete: (id: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/courses/${id}`);
  }
};

// Assignment API
export const assignmentAPI = {
  getAll: (): Promise<AxiosResponse<any>> => {
    return api.get('/assignments');
  },

  getById: (id: string): Promise<AxiosResponse<Assignment>> => {
    return api.get(`/assignments/${id}`);
  },

  create: (assignmentData: Partial<Assignment>): Promise<AxiosResponse<Assignment>> => {
    return api.post('/assignments', assignmentData);
  },

  update: (id: string, assignmentData: Partial<Assignment>): Promise<AxiosResponse<Assignment>> => {
    return api.put(`/assignments/${id}`, assignmentData);
  },

  delete: (id: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/assignments/${id}`);
  }
};

// Announcement API
export const announcementAPI = {
  getAll: (): Promise<AxiosResponse<{ data: Announcement[] }>> => {
    return api.get('/announcements');
  },

  getById: (id: string): Promise<AxiosResponse<{ data: Announcement }>> => {
    return api.get(`/announcements/${id}`);
  },

  create: (announcementData: Partial<Announcement>): Promise<AxiosResponse<{ data: Announcement }>> => {
    return api.post('/announcements', announcementData);
  },

  update: (id: string, announcementData: Partial<Announcement>): Promise<AxiosResponse<{ data: Announcement }>> => {
    return api.put(`/announcements/${id}`, announcementData);
  },

  delete: (id: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/announcements/${id}`);
  }
};

// Library Resource API
export const libraryResourceAPI = {
 getAll: (): Promise<AxiosResponse<any>> => {
    return api.get('/library-resources');
  },

  getById: (id: string): Promise<AxiosResponse<LibraryResource>> => {
    return api.get(`/library-resources/${id}`);
  },

  create: (resourceData: Partial<LibraryResource>): Promise<AxiosResponse<LibraryResource>> => {
    return api.post('/library-resources', resourceData);
  },

  update: (id: string, resourceData: Partial<LibraryResource>): Promise<AxiosResponse<LibraryResource>> => {
    return api.put(`/library-resources/${id}`, resourceData);
  },

  delete: (id: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/library-resources/${id}`);
  }
};

// Discussion Thread API
export const discussionThreadAPI = {
  getAll: (): Promise<AxiosResponse<any>> => {
    return api.get('/discussion-threads');
  },

  getById: (id: string): Promise<AxiosResponse<DiscussionThread>> => {
    return api.get(`/discussion-threads/${id}`);
  },

  create: (threadData: Partial<DiscussionThread>): Promise<AxiosResponse<DiscussionThread>> => {
    return api.post('/discussion-threads', threadData);
  },

  update: (id: string, threadData: Partial<DiscussionThread>): Promise<AxiosResponse<DiscussionThread>> => {
    return api.put(`/discussion-threads/${id}`, threadData);
  },

  delete: (id: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/discussion-threads/${id}`);
  }
};

// Notification API
export const notificationAPI = {
  getAll: (): Promise<AxiosResponse<any>> => {
    return api.get('/notifications');
  },

  getById: (id: string): Promise<AxiosResponse<Notification>> => {
    return api.get(`/notifications/${id}`);
  },

  create: (notificationData: Partial<Notification>): Promise<AxiosResponse<Notification>> => {
    return api.post('/notifications', notificationData);
  },

  update: (id: string, notificationData: Partial<Notification>): Promise<AxiosResponse<Notification>> => {
    return api.put(`/notifications/${id}`, notificationData);
  },

  delete: (id: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/notifications/${id}`);
  },

  markAsRead: (id: string): Promise<AxiosResponse<Notification>> => {
    return api.patch(`/notifications/${id}/read`);
  },

  markAllAsRead: (): Promise<AxiosResponse<{ message: string }>> => {
    return api.patch('/notifications/mark-all-read');
  }
};

// Grade API
export const gradeAPI = {
  getAll: (): Promise<AxiosResponse<any>> => {
    return api.get('/grades');
  },

  getById: (id: string): Promise<AxiosResponse<any>> => {
    return api.get(`/grades/${id}`);
  },

  create: (gradeData: Partial<Grade>): Promise<AxiosResponse<any>> => {
    return api.post('/grades', gradeData);
  },

  update: (id: string, gradeData: Partial<Grade>): Promise<AxiosResponse<any>> => {
    return api.put(`/grades/${id}`, gradeData);
  },

  delete: (id: string): Promise<AxiosResponse<{ message: string }>> => {
    return api.delete(`/grades/${id}`);
  }
};

// Calendar Event API
export const calendarEventAPI = {
 getAll: (params?: { type?: string; is_academic?: boolean }): Promise<AxiosResponse<any>> => {
    return api.get('/calendar-events', { params });
  },

  getById: (id: string): Promise<AxiosResponse<any>> => {
    return api.get(`/calendar-events/${id}`);
  },

  create: (eventData: Partial<CalendarEvent>): Promise<AxiosResponse<any>> => {
    return api.post('/calendar-events', eventData);
  },

  update: (id: string, eventData: Partial<CalendarEvent>): Promise<AxiosResponse<any>> => {
    return api.put(`/calendar-events/${id}`, eventData);
  },

  delete: (id: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/calendar-events/${id}`);
  }
};

// Faculty API
export const facultyAPI = {
  getAll: (): Promise<AxiosResponse<any>> => {
    return api.get('/faculties');
  },

  getById: (id: string): Promise<AxiosResponse<Faculty>> => {
    return api.get(`/faculties/${id}`);
  },

  create: (facultyData: Partial<Faculty>): Promise<AxiosResponse<Faculty>> => {
    return api.post('/faculties', facultyData);
  },

  update: (id: string, facultyData: Partial<Faculty>): Promise<AxiosResponse<Faculty>> => {
    return api.put(`/faculties/${id}`, facultyData);
  },

  delete: (id: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/faculties/${id}`);
  }
};

// Major API
export const majorAPI = {
  getAll: (): Promise<AxiosResponse<any>> => {
    return api.get('/majors');
  },

  getById: (id: string): Promise<AxiosResponse<Major>> => {
    return api.get(`/majors/${id}`);
  },

  create: (majorData: Partial<Major>): Promise<AxiosResponse<Major>> => {
    return api.post('/majors', majorData);
  },

  update: (id: string, majorData: Partial<Major>): Promise<AxiosResponse<Major>> => {
    return api.put(`/majors/${id}`, majorData);
  },

  delete: (id: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/majors/${id}`);
  }
};

// Dashboard Analytics API
export const dashboardAPI = {
  getDosenStats: (instructorName: string): Promise<AxiosResponse<any>> => {
    return api.get(`/dashboard/dosen/${instructorName}`);
  },

  getManajemenStats: (): Promise<AxiosResponse<any>> => {
    return api.get('/dashboard/manajemen');
  },

  getGradeDistribution: (courseId?: string): Promise<AxiosResponse<any>> => {
    const params = courseId ? { course_id: courseId } : {};
    return api.get('/dashboard/grade-distribution', { params });
  },

  getFacultyEnrollment: (): Promise<AxiosResponse<any>> => {
    return api.get('/dashboard/faculty-enrollment');
  }
};

// Course Module API
export const courseModuleAPI = {
  getAll: (courseId: string): Promise<AxiosResponse<any>> => {
    return api.get(`/courses/${courseId}/modules`);
  },

  getById: (moduleId: string): Promise<AxiosResponse<CourseModule>> => {
    return api.get(`/modules/${moduleId}`);
  },

  create: (courseId: string, moduleData: Partial<CourseModule>): Promise<AxiosResponse<CourseModule>> => {
    return api.post(`/courses/${courseId}/modules`, moduleData);
  },

  update: (moduleId: string, moduleData: Partial<CourseModule>): Promise<AxiosResponse<CourseModule>> => {
    return api.put(`/modules/${moduleId}`, moduleData);
  },

  delete: (moduleId: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/modules/${moduleId}`);
  },

  reorder: (courseId: string, moduleIds: string[]): Promise<AxiosResponse<any>> => {
    return api.post(`/courses/${courseId}/modules/reorder`, { module_ids: moduleIds });
  }
};

// Payment API
export const paymentAPI = {
  createTransaction: (order_id: string, amount: number): Promise<AxiosResponse<{ snap_token: string }>> => {
    return api.post('/payment/create-transaction', { order_id, amount });
  },

  checkTransactionStatus: (order_id: string): Promise<AxiosResponse<any>> => {
    return api.get(`/payment/status/${order_id}`);
  }
};

// Default export for convenience
export default api;
