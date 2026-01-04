import axios, { AxiosResponse } from 'axios';
import { 
  User, 
  Course, 
  Assignment, 
  Announcement, 
  LibraryResource, 
  DiscussionThread, 
  DiscussionPost,
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

// Helper function to map backend role to frontend role
const mapBackendRoleToFrontend = (backendRole: string): User['role'] => {
  const roleMap: Record<string, User['role']> = {
    'student': 'Mahasiswa',
    'dosen': 'Dosen',
    'lecturer': 'Dosen',
    'admin': 'Manajemen Kampus',
    'prodi_admin': 'Prodi Admin',
    'super_admin': 'Super Admin',
    'maba': 'MABA',
    'MABA': 'MABA', // Backend returns uppercase MABA
  };
  const mappedRole = roleMap[backendRole];
  // Return mapped role or default to 'Mahasiswa', ensuring it's a valid UserRole
  return mappedRole ?? 'Mahasiswa';
};

// Helper function to map frontend role to backend role
const mapFrontendRoleToBackend = (frontendRole: User['role']): string => {
  const roleMap: Record<User['role'], string> = {
    'Mahasiswa': 'student',
    'Dosen': 'dosen',
    'Manajemen Kampus': 'admin',
    'Prodi Admin': 'prodi_admin',
    'Super Admin': 'super_admin',
    'MABA': 'maba'
  };
  return roleMap[frontendRole] || 'student';
};

// Helper function to transform user data
const transformUser = (userData: any): User => {
  const mappedRole = mapBackendRoleToFrontend(userData.role || 'student');
  const transformed = {
    ...userData,
    role: mappedRole
  };
  // Debug: log to ensure id is preserved
  console.log('Transforming user:', userData, '->', transformed);
  return transformed as User;
};

// ============================================================================
// AUTHENTICATION API
// ============================================================================

export const authAPI = {
  login: (email: string, password: string): Promise<AxiosResponse<{ token: string; user: User }>> => {
    return api.post('/login', { email, password }).then(response => ({
      ...response,
      data: {
        ...response.data,
        user: transformUser(response.data.user)
      }
    }));
  },

  register: (userData: { name: string; email: string; phone_number: string; password: string; password_confirmation: string; role?: User['role'] }): Promise<AxiosResponse<{ token: string; user: User }>> => {
    // Map role if provided
    const payload: any = {
      name: userData.name,
      email: userData.email,
      phone: userData.phone_number, // Backend expects 'phone', frontend uses 'phone_number'
      password: userData.password,
      password_confirmation: userData.password_confirmation
    };
    if (userData.role) {
      payload.role = mapFrontendRoleToBackend(userData.role);
    }
    return api.post('/register', payload).then(response => ({
      ...response,
      data: {
        ...response.data,
        user: transformUser(response.data.user)
      }
    }));
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
    return api.get('/me/profile').then(response => ({
      ...response,
      data: transformUser(response.data)
    }));
  },

  getCurrentUser: (): Promise<AxiosResponse<User>> => {
    return api.get('/user').then(response => {
      // Backend returns wrapped response: { success, message, data }
      const userData = response.data.data || response.data;
      return {
        ...response,
        data: transformUser(userData)
      };
    });
  }
};

// ============================================================================
// USER API
// ============================================================================

export const userAPI = {
  getAll: (): Promise<AxiosResponse<User[]>> => {
    return api.get('/users').then(response => {
      const users = response.data.data || response.data;
      return {
        ...response,
        data: Array.isArray(users) ? users.map(transformUser) : users
      };
    });
  },

  getById: (id: string): Promise<AxiosResponse<User>> => {
    return api.get(`/users/${id}`).then(response => ({
      ...response,
      data: transformUser(response.data)
    }));
  },

  getMe: (): Promise<AxiosResponse<User>> => {
    return api.get('/users/me/profile').then(response => ({
      ...response,
      data: transformUser(response.data)
    }));
  },

  updateMe: (userData: Partial<User>): Promise<AxiosResponse<User>> => {
    // Map frontend field names to backend field names
    const payload: any = { ...userData };
    if (payload.avatarUrl !== undefined) {
      payload.avatar = payload.avatarUrl;
      delete payload.avatarUrl;
    }
    return api.put('/users/me/profile', payload).then(response => ({
      ...response,
      data: transformUser(response.data?.data || response.data)
    }));
  },

  changePassword: (currentPassword: string, newPassword: string): Promise<AxiosResponse<{ message: string }>> => {
    return api.post('/users/me/change-password', {
      current_password: currentPassword,
      password: newPassword
    });
  },

  create: (userData: Partial<User>): Promise<AxiosResponse<User>> => {
    // Map role and phone number
    const payload: any = { ...userData };
    if (payload.role) {
      payload.role = mapFrontendRoleToBackend(payload.role);
    }
    // Map phoneNumber to phone for backend compatibility
    if (payload.phoneNumber !== undefined) {
      payload.phone = payload.phoneNumber;
      delete payload.phoneNumber;
    }
    return api.post('/users', payload).then(response => ({
      ...response,
      data: transformUser(response.data)
    }));
  },

  update: (id: string, userData: Partial<User>): Promise<AxiosResponse<User>> => {
    const payload: any = { ...userData };
    if (payload.role) {
      payload.role = mapFrontendRoleToBackend(payload.role);
    }
    // Map phoneNumber to phone for backend compatibility
    if (payload.phoneNumber !== undefined) {
      payload.phone = payload.phoneNumber;
      delete payload.phoneNumber;
    }
    return api.put(`/users/${id}`, payload).then(response => ({
      ...response,
      data: transformUser(response.data)
    }));
  },

  delete: (id: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/users/${id}`);
  },

  getByRole: (role: User['role']): Promise<AxiosResponse<User[]>> => {
    const backendRole = mapFrontendRoleToBackend(role);
    return api.get(`/users/role/${backendRole}`).then(response => {
      const users = response.data.data || response.data;
      return {
        ...response,
        data: Array.isArray(users) ? users.map(transformUser) : users
      };
    });
  },

  getByFaculty: (facultyId: string): Promise<AxiosResponse<User[]>> => {
    return api.get(`/users/faculty/${facultyId}`).then(response => ({
      ...response,
      data: response.data.map(transformUser)
    }));
  },

  getByMajor: (majorId: string): Promise<AxiosResponse<User[]>> => {
    return api.get(`/users/major/${majorId}`).then(response => ({
      ...response,
      data: response.data.map(transformUser)
    }));
  },

  getFacultyList: (): Promise<AxiosResponse<User[]>> => {
    return api.get('/users/list/faculty').then(response => ({
      ...response,
      data: response.data.map(transformUser)
    }));
  },

  getStudents: (): Promise<AxiosResponse<User[]>> => {
    return api.get('/users/list/students').then(response => ({
      ...response,
      data: response.data.map(transformUser)
    }));
  }
};

// ============================================================================
// FACULTY API
// ============================================================================

export const facultyAPI = {
  getPublic: (): Promise<AxiosResponse<Faculty[]>> => {
    return api.get('/public/faculties').then(response => ({
      ...response,
      data: response.data.data || []
    }));
  },

  getAll: (): Promise<AxiosResponse<Faculty[]>> => {
    return api.get('/faculties').then(response => ({
      ...response,
      data: response.data.data || []
    }));
  },

  getById: (id: string): Promise<AxiosResponse<Faculty>> => {
    return api.get(`/faculties/${id}`).then(response => ({
      ...response,
      data: response.data.data || response.data
    }));
  },

  getMajors: (id: string): Promise<AxiosResponse<Major[]>> => {
    return api.get(`/faculties/${id}/majors`).then(response => ({
      ...response,
      data: response.data.data || []
    }));
  },

  getCourses: (id: string): Promise<AxiosResponse<Course[]>> => {
    return api.get(`/faculties/${id}/courses`).then(response => ({
      ...response,
      data: response.data.data || []
    }));
  },

  create: (facultyData: Partial<Faculty>): Promise<AxiosResponse<Faculty>> => {
    return api.post('/faculties', facultyData).then(response => ({
      ...response,
      data: response.data.data || response.data
    }));
  },

  update: (id: string, facultyData: Partial<Faculty>): Promise<AxiosResponse<Faculty>> => {
    return api.put(`/faculties/${id}`, facultyData).then(response => ({
      ...response,
      data: response.data.data || response.data
    }));
  },

  delete: (id: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/faculties/${id}`);
  }
};

// ============================================================================
// MAJOR API
// ============================================================================

export const majorAPI = {
  getAll: (): Promise<AxiosResponse<Major[]>> => {
    return api.get('/majors');
  },

  getById: (code: string): Promise<AxiosResponse<Major>> => {
    return api.get(`/majors/${code}`);
  },

  getFaculty: (code: string): Promise<AxiosResponse<Faculty>> => {
    return api.get(`/majors/${code}/faculty`);
  },

  getCourses: (code: string): Promise<AxiosResponse<Course[]>> => {
    return api.get(`/majors/${code}/courses`);
  },

  create: (majorData: Partial<Major>): Promise<AxiosResponse<Major>> => {
    return api.post('/majors', majorData);
  },

  update: (code: string, majorData: Partial<Major>): Promise<AxiosResponse<Major>> => {
    return api.put(`/majors/${code}`, majorData);
  },

  delete: (code: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/majors/${code}`);
  }
};

// ============================================================================
// COURSE API
// ============================================================================

export const courseAPI = {
  getAll: (params?: { facultyId?: string; status?: string; search?: string; majorId?: string }): Promise<AxiosResponse<Course[]>> => {
    return api.get('/courses', { params }).then(response => ({
      ...response,
      data: response.data.data || response.data || []
    }));
  },

  getPublic: (params?: { search?: string }): Promise<AxiosResponse<Course[]>> => {
    return api.get('/public/courses', { params }).then(response => ({
      ...response,
      data: response.data.data || []
    }));
  },

  getById: (id: string): Promise<AxiosResponse<Course>> => {
    return api.get(`/courses/${id}`);
  },

  getMyCourses: (): Promise<AxiosResponse<Course[]>> => {
    return api.get('/courses/my-courses');
  },

  getModules: (id: string): Promise<AxiosResponse<CourseModule[]>> => {
    return api.get(`/courses/${id}/modules`);
  },

  getEnrollments: (id: string): Promise<AxiosResponse<any[]>> => {
    return api.get(`/courses/${id}/enrollments`);
  },

  getStudents: (id: string): Promise<AxiosResponse<User[]>> => {
    return api.get(`/courses/${id}/students`).then(response => ({
      ...response,
      data: response.data.map(transformUser)
    }));
  },

  getAssignments: (id: string): Promise<AxiosResponse<Assignment[]>> => {
    return api.get(`/courses/${id}/assignments`).then(response => {
      const data = response.data?.data ?? response.data ?? response;
      const assignments = Array.isArray(data) ? data : [];
      return {
        ...response,
        data: assignments.map(transformAssignment)
      };
    });
  },

  getAnnouncements: (id: string): Promise<AxiosResponse<Announcement[]>> => {
    return api.get(`/courses/${id}/announcements`);
  },

  getLibraryResources: (id: string): Promise<AxiosResponse<LibraryResource[]>> => {
    return api.get(`/courses/${id}/library-resources`);
  },

  getDiscussionThreads: (id: string): Promise<AxiosResponse<DiscussionThread[]>> => {
    return api.get(`/courses/${id}/discussion-threads`);
  },

  getGrades: (id: string): Promise<AxiosResponse<Grade[]>> => {
    return api.get(`/courses/${id}/grades`);
  },

  getStudentProgress: (id: string): Promise<AxiosResponse<any[]>> => {
    return api.get(`/courses/${id}/student-progress`);
  },

  getAssignmentsWithStats: (id: string): Promise<AxiosResponse<any[]>> => {
    return api.get(`/courses/${id}/assignments-with-stats`);
  },

  create: (courseData: Partial<Course>): Promise<AxiosResponse<Course>> => {
    return api.post('/courses', courseData);
  },

  update: (id: string, courseData: Partial<Course>): Promise<AxiosResponse<Course>> => {
    return api.put(`/courses/${id}`, courseData);
  },

  delete: (id: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/courses/${id}`);
  },

  toggleStatus: (id: string): Promise<AxiosResponse<Course>> => {
    return api.post(`/courses/${id}/toggle-status`);
  },

  enroll: (id: string): Promise<AxiosResponse<{ message: string }>> => {
    return api.post(`/courses/${id}/enroll`);
  },

  drop: (id: string): Promise<AxiosResponse<{ message: string }>> => {
    return api.post(`/courses/${id}/drop`);
  }
};

// ============================================================================
// HELPER: Extract data from API response
// ============================================================================

const extractData = <T>(response: any): T => {
  return response.data?.data ?? response.data ?? response;
};

const extractArrayData = <T>(response: any): T[] => {
  const data = response.data?.data ?? response.data ?? response;
  return Array.isArray(data) ? data : [];
};

// ============================================================================
// HELPER: Transform module data from backend to frontend format
// ============================================================================

const transformModule = (module: any): CourseModule => ({
  id: String(module.id),
  title: module.title,
  type: module.type || 'video',
  description: module.description,
  duration: module.duration,
  resourceUrl: module.resourceUrl || module.video_url || module.document_url,
  captionsUrl: module.captionsUrl || module.captions_url,
  attachmentUrl: module.attachmentUrl || module.attachment_url,
  startTime: module.startTime || module.start_time,
  liveUrl: module.liveUrl || module.live_url,
});

const transformModuleToBackend = (module: Partial<CourseModule>): any => ({
  title: module.title,
  type: module.type,
  description: module.description || null,
  duration: module.duration || null,
  video_url: module.type !== 'live' ? module.resourceUrl : null,
  document_url: module.type === 'pdf' ? module.resourceUrl : null,
  captions_url: module.captionsUrl || null,
  attachment_url: module.attachmentUrl || null,
  start_time: module.startTime || null,
  live_url: module.liveUrl || null,
  is_published: true,
});

// ============================================================================
// HELPER: Transform assignment data from backend to frontend format
// ============================================================================

const transformAssignment = (assignment: any): Assignment => ({
  id: String(assignment.id),
  courseId: String(assignment.courseId || assignment.course_id),
  title: assignment.title,
  description: assignment.description,
  dueDate: assignment.dueDate || assignment.due_date,
  files: assignment.files || [],
  submissions: assignment.submissions || [],
  type: assignment.type || assignment.submission_type || 'file',
  category: assignment.category || 'Tugas',
  maxScore: assignment.maxScore || assignment.max_points || 100,
  instructions: assignment.instructions,
  attachments: assignment.attachments || [],
  created_at: assignment.created_at,
  updated_at: assignment.updated_at,
});

const transformAssignmentToBackend = (assignment: Partial<Assignment>): any => ({
  course_id: assignment.courseId,
  title: assignment.title,
  description: assignment.description,
  due_date: assignment.dueDate,
  submission_type: assignment.type || 'file',
  category: assignment.category || 'Tugas',
  max_points: assignment.maxScore || 100,
  instructions: assignment.instructions || assignment.description,
  is_published: true,
});

// ============================================================================
// HELPER: Transform discussion data from backend to frontend format
// ============================================================================

const transformThread = (thread: any): DiscussionThread => ({
  id: String(thread.id),
  courseId: String(thread.courseId || thread.course_id),
  title: thread.title,
  authorId: String(thread.authorId || thread.created_by),
  createdAt: thread.createdAt || thread.created_at,
  isPinned: thread.isPinned || thread.is_pinned || false,
  isClosed: thread.isClosed || thread.status === 'closed' || false,
  posts: (thread.posts || []).map(transformPost),
});

const transformPost = (post: any): DiscussionPost => ({
  id: String(post.id),
  authorId: String(post.authorId || post.user_id),
  createdAt: post.createdAt || post.created_at,
  content: post.content,
});

const transformThreadToBackend = (thread: Partial<DiscussionThread> & { content?: string }): any => ({
  course_id: thread.courseId,
  title: thread.title,
  content: thread.content,
  status: thread.isClosed ? 'closed' : 'open',
  is_pinned: thread.isPinned || false,
});

// ============================================================================
// COURSE MODULE API
// ============================================================================

export const courseModuleAPI = {
  getAll: (courseId: string): Promise<AxiosResponse<CourseModule[]>> => {
    return api.get(`/courses/${courseId}/modules`);
  },

  getById: (moduleId: string): Promise<AxiosResponse<CourseModule>> => {
    return api.get(`/modules/${moduleId}`);
  },

  getAssignments: (moduleId: string): Promise<AxiosResponse<Assignment[]>> => {
    return api.get(`/modules/${moduleId}/assignments`);
  },

  getDiscussions: (moduleId: string): Promise<AxiosResponse<DiscussionThread[]>> => {
    return api.get(`/modules/${moduleId}/discussions`);
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

// ============================================================================
// ASSIGNMENT API
// ============================================================================

export const assignmentAPI = {
  getAll: (params?: { course_id?: string }): Promise<AxiosResponse<Assignment[]>> => {
    return api.get('/assignments', { params }).then(response => {
      const data = response.data?.data ?? response.data ?? response;
      const assignments = Array.isArray(data) ? data : [];
      return {
        ...response,
        data: assignments.map(transformAssignment)
      };
    });
  },

  getById: (id: string): Promise<AxiosResponse<Assignment>> => {
    return api.get(`/assignments/${id}`).then(response => {
      const data = response.data?.data ?? response.data ?? response;
      return {
        ...response,
        data: transformAssignment(data)
      };
    });
  },

  getSubmissions: (id: string): Promise<AxiosResponse<any[]>> => {
    return api.get(`/assignments/${id}/submissions`);
  },

  create: (assignmentData: Partial<Assignment>): Promise<AxiosResponse<Assignment>> => {
    return api.post('/assignments', assignmentData);
  },

  update: (id: string, assignmentData: Partial<Assignment>): Promise<AxiosResponse<Assignment>> => {
    return api.put(`/assignments/${id}`, assignmentData);
  },

  delete: (id: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/assignments/${id}`);
  },

  publish: (id: string): Promise<AxiosResponse<Assignment>> => {
    return api.post(`/assignments/${id}/publish`);
  },

  unpublish: (id: string): Promise<AxiosResponse<Assignment>> => {
    return api.post(`/assignments/${id}/unpublish`);
  },

  submit: (id: string, submissionData: any): Promise<AxiosResponse<any>> => {
    return api.post(`/assignments/${id}/submit`, submissionData);
  },

  getMySubmission: (id: string): Promise<AxiosResponse<any>> => {
    return api.get(`/assignments/${id}/my-submission`);
  }
};

// ============================================================================
// ANNOUNCEMENT API
// ============================================================================

export const announcementAPI = {
  getAll: (params?: { course_id?: string }): Promise<AxiosResponse<Announcement[]>> => {
    return api.get('/announcements', { params });
  },

  getById: (id: string): Promise<AxiosResponse<Announcement>> => {
    return api.get(`/announcements/${id}`);
  },

  create: (announcementData: Partial<Announcement>): Promise<AxiosResponse<Announcement>> => {
    return api.post('/announcements', announcementData);
  },

  update: (id: string, announcementData: Partial<Announcement>): Promise<AxiosResponse<Announcement>> => {
    return api.put(`/announcements/${id}`, announcementData);
  },

  delete: (id: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/announcements/${id}`);
  },

  publish: (id: string): Promise<AxiosResponse<Announcement>> => {
    return api.post(`/announcements/${id}/publish`);
  },

  unpublish: (id: string): Promise<AxiosResponse<Announcement>> => {
    return api.post(`/announcements/${id}/unpublish`);
  },

  markRead: (id: string): Promise<AxiosResponse<Announcement>> => {
    return api.post(`/announcements/${id}/mark-read`);
  }
};

// ============================================================================
// LIBRARY RESOURCE API
// ============================================================================

export const libraryResourceAPI = {
  getAll: (params?: { course_id?: string }): Promise<AxiosResponse<LibraryResource[]>> => {
    return api.get('/library-resources', { params }).then(response => ({
      ...response,
      data: response.data.data || response.data
    }));
  },

  getById: (id: string): Promise<AxiosResponse<LibraryResource>> => {
    return api.get(`/library-resources/${id}`).then(response => ({
      ...response,
      data: response.data.data || response.data
    }));
  },

  create: (resourceData: Partial<LibraryResource>): Promise<AxiosResponse<LibraryResource>> => {
    return api.post('/library-resources', resourceData).then(response => ({
      ...response,
      data: response.data.data || response.data
    }));
  },

  update: (id: string, resourceData: Partial<LibraryResource>): Promise<AxiosResponse<LibraryResource>> => {
    return api.put(`/library-resources/${id}`, resourceData).then(response => ({
      ...response,
      data: response.data.data || response.data
    }));
  },

  delete: (id: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/library-resources/${id}`);
  },

  publish: (id: string): Promise<AxiosResponse<LibraryResource>> => {
    return api.post(`/library-resources/${id}/publish`).then(response => ({
      ...response,
      data: response.data.data || response.data
    }));
  },

  unpublish: (id: string): Promise<AxiosResponse<LibraryResource>> => {
    return api.post(`/library-resources/${id}/unpublish`).then(response => ({
      ...response,
      data: response.data.data || response.data
    }));
  },

  download: (id: string): Promise<AxiosResponse<any>> => {
    return api.post(`/library-resources/${id}/download`).then(response => ({
      ...response,
      data: response.data.data || response.data
    }));
  }
};

// ============================================================================
// DISCUSSION THREAD API
// ============================================================================

export const discussionThreadAPI = {
  getAll: (params?: { course_id?: string; module_id?: string }): Promise<AxiosResponse<DiscussionThread[]>> => {
    return api.get('/discussion-threads', { params });
  },

  getById: (id: string): Promise<AxiosResponse<DiscussionThread>> => {
    return api.get(`/discussion-threads/${id}`);
  },

  getPosts: (id: string): Promise<AxiosResponse<any[]>> => {
    return api.get(`/discussion-threads/${id}/posts`);
  },

  create: (threadData: Partial<DiscussionThread>): Promise<AxiosResponse<DiscussionThread>> => {
    return api.post('/discussion-threads', threadData);
  },

  update: (id: string, threadData: Partial<DiscussionThread>): Promise<AxiosResponse<DiscussionThread>> => {
    return api.put(`/discussion-threads/${id}`, threadData);
  },

  delete: (id: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/discussion-threads/${id}`);
  },

  close: (id: string): Promise<AxiosResponse<DiscussionThread>> => {
    return api.post(`/discussion-threads/${id}/close`);
  },

  reopen: (id: string): Promise<AxiosResponse<DiscussionThread>> => {
    return api.post(`/discussion-threads/${id}/reopen`);
  },

  pin: (id: string): Promise<AxiosResponse<DiscussionThread>> => {
    return api.post(`/discussion-threads/${id}/pin`);
  },

  unpin: (id: string): Promise<AxiosResponse<DiscussionThread>> => {
    return api.post(`/discussion-threads/${id}/unpin`);
  },

  lock: (id: string): Promise<AxiosResponse<DiscussionThread>> => {
    return api.post(`/discussion-threads/${id}/lock`);
  },

  unlock: (id: string): Promise<AxiosResponse<DiscussionThread>> => {
    return api.post(`/discussion-threads/${id}/unlock`);
  },

  archive: (id: string): Promise<AxiosResponse<DiscussionThread>> => {
    return api.post(`/discussion-threads/${id}/archive`);
  },

  restore: (id: string): Promise<AxiosResponse<DiscussionThread>> => {
    return api.post(`/discussion-threads/${id}/restore`);
  },

  getMyThreads: (): Promise<AxiosResponse<DiscussionThread[]>> => {
    return api.get('/discussion-threads/my-threads');
  },

  getByCourse: (courseId: string): Promise<AxiosResponse<DiscussionThread[]>> => {
    return api.get(`/discussion-threads/by-course/${courseId}`).then(response => ({
      ...response,
      data: response.data.data || response.data
    }));
  },

  getByModule: (moduleId: string): Promise<AxiosResponse<DiscussionThread[]>> => {
    return api.get(`/discussion-threads/by-module/${moduleId}`).then(response => ({
      ...response,
      data: response.data.data || response.data
    }));
  }
};

// ============================================================================
// DISCUSSION POST API
// ============================================================================

export const discussionPostAPI = {
  createPost: (threadId: string, postData: { content: string }): Promise<AxiosResponse<any>> => {
    return api.post(`/discussion-threads/${threadId}/posts`, postData);
  },

  getById: (id: string): Promise<AxiosResponse<any>> => {
    return api.get(`/discussion-posts/${id}`);
  },

  getReplies: (id: string): Promise<AxiosResponse<any[]>> => {
    return api.get(`/discussion-posts/${id}/replies`);
  },

  reply: (id: string, replyData: { content: string }): Promise<AxiosResponse<any>> => {
    return api.post(`/discussion-posts/${id}/reply`, replyData);
  },

  getMyPosts: (): Promise<AxiosResponse<any[]>> => {
    return api.get('/discussion-posts/my-posts');
  },

  getByThread: (threadId: string): Promise<AxiosResponse<any[]>> => {
    return api.get(`/discussion-posts/by-thread/${threadId}`).then(response => ({
      ...response,
      data: response.data.data || response.data
    }));
  },

  getSolutionByThread: (threadId: string): Promise<AxiosResponse<any>> => {
    return api.get(`/discussion-posts/solution-by-thread/${threadId}`).then(response => ({
      ...response,
      data: response.data.data || response.data
    }));
  },

  update: (id: string, postData: { content: string }): Promise<AxiosResponse<any>> => {
    return api.put(`/discussion-posts/${id}`, postData);
  },

  delete: (id: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/discussion-posts/${id}`);
  },

  like: (id: string): Promise<AxiosResponse<any>> => {
    return api.post(`/discussion-posts/${id}/like`);
  },

  unlike: (id: string): Promise<AxiosResponse<any>> => {
    return api.post(`/discussion-posts/${id}/unlike`);
  },

  markAsSolution: (id: string): Promise<AxiosResponse<any>> => {
    return api.post(`/discussion-posts/${id}/mark-as-solution`);
  },

  unmarkAsSolution: (id: string): Promise<AxiosResponse<any>> => {
    return api.post(`/discussion-posts/${id}/unmark-as-solution`);
  }
};

// ============================================================================
// NOTIFICATION API
// ============================================================================

export const notificationAPI = {
  getAll: (): Promise<AxiosResponse<Notification[]>> => {
    return api.get('/notifications').then(response => ({
      ...response,
      data: response.data?.data || response.data || []
    }));
  },

  getById: (id: string): Promise<AxiosResponse<Notification>> => {
    return api.get(`/notifications/${id}`).then(response => ({
      ...response,
      data: response.data?.data || response.data
    }));
  },

  create: (notificationData: Partial<Notification>): Promise<AxiosResponse<Notification>> => {
    return api.post('/notifications', notificationData).then(response => ({
      ...response,
      data: response.data?.data || response.data
    }));
  },

  update: (id: string, notificationData: Partial<Notification>): Promise<AxiosResponse<Notification>> => {
    return api.put(`/notifications/${id}`, notificationData).then(response => ({
      ...response,
      data: response.data?.data || response.data
    }));
  },

  delete: (id: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/notifications/${id}`);
  },

  markRead: (id: string): Promise<AxiosResponse<Notification>> => {
    return api.put(`/notifications/${id}/read`).then(response => ({
      ...response,
      data: response.data?.data || response.data
    }));
  },

  markUnread: (id: string): Promise<AxiosResponse<Notification>> => {
    return api.post(`/notifications/${id}/mark-unread`).then(response => ({
      ...response,
      data: response.data?.data || response.data
    }));
  },

  markAllRead: (): Promise<AxiosResponse<{ message: string }>> => {
    return api.put('/notifications/mark-all-read');
  },

  getUnread: (): Promise<AxiosResponse<Notification[]>> => {
    return api.get('/notifications/unread').then(response => ({
      ...response,
      data: response.data?.data || response.data || []
    }));
  },

  getUrgent: (): Promise<AxiosResponse<Notification[]>> => {
    return api.get('/notifications/urgent').then(response => ({
      ...response,
      data: response.data?.data || response.data || []
    }));
  },

  getCounts: (): Promise<AxiosResponse<any>> => {
    return api.get('/notifications/counts').then(response => ({
      ...response,
      data: response.data?.data || response.data
    }));
  },

  clearRead: (): Promise<AxiosResponse<{ message: string }>> => {
    return api.delete('/notifications/clear-read');
  }
};

// ============================================================================
// GRADE API
// ============================================================================

export const gradeAPI = {
  getAll: (params?: { course_id?: string; student_id?: string }): Promise<AxiosResponse<Grade[]>> => {
    return api.get('/grades', { params });
  },

  getById: (id: string): Promise<AxiosResponse<Grade>> => {
    return api.get(`/grades/${id}`);
  },

  getMyGrades: (): Promise<AxiosResponse<Grade[]>> => {
    return api.get('/grades/my-grades');
  },

  getByCourse: (courseId: string): Promise<AxiosResponse<Grade[]>> => {
    return api.get(`/grades/course/${courseId}`);
  },

  getByAssignment: (assignmentId: string): Promise<AxiosResponse<Grade[]>> => {
    return api.get(`/grades/assignment/${assignmentId}`);
  },

  getByStudent: (studentId: string): Promise<AxiosResponse<Grade[]>> => {
    return api.get(`/grades/student/${studentId}`);
  },

  getDistribution: (courseId: string): Promise<AxiosResponse<any>> => {
    return api.get(`/grades/distribution/${courseId}`);
  },

  getAnalyticsByFaculty: (): Promise<AxiosResponse<any>> => {
    return api.get('/grades/analytics/faculty');
  },

  getAnalyticsByCourse: (): Promise<AxiosResponse<any>> => {
    return api.get('/grades/analytics/course');
  },

  create: (gradeData: Partial<Grade>): Promise<AxiosResponse<Grade>> => {
    return api.post('/grades', gradeData);
  },

  update: (id: string, gradeData: Partial<Grade>): Promise<AxiosResponse<Grade>> => {
    return api.put(`/grades/${id}`, gradeData);
  },

  delete: (id: string): Promise<AxiosResponse<{ message: string }>> => {
    return api.delete(`/grades/${id}`);
  }
};

// ============================================================================
// CALENDAR EVENT API
// ============================================================================

export const calendarEventAPI = {
  getAll: (params?: { type?: string; is_academic?: boolean }): Promise<AxiosResponse<CalendarEvent[]>> => {
    return api.get('/calendar-events', { params });
  },

  getById: (id: string): Promise<AxiosResponse<CalendarEvent>> => {
    return api.get(`/calendar-events/${id}`);
  },

  create: (eventData: Partial<CalendarEvent>): Promise<AxiosResponse<CalendarEvent>> => {
    return api.post('/calendar-events', eventData);
  },

  update: (id: string, eventData: Partial<CalendarEvent>): Promise<AxiosResponse<CalendarEvent>> => {
    return api.put(`/calendar-events/${id}`, eventData);
  },

  delete: (id: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/calendar-events/${id}`);
  }
};

// ============================================================================
// DASHBOARD API
// ============================================================================

export const dashboardAPI = {
  getStats: (): Promise<AxiosResponse<any>> => {
    return api.get('/dashboard');
  },

  getStudentStats: (): Promise<AxiosResponse<any>> => {
    return api.get('/dashboard/student');
  },

  getFacultyStats: (): Promise<AxiosResponse<any>> => {
    return api.get('/dashboard/faculty');
  },

  getProdiStats: (facultyId?: string): Promise<AxiosResponse<any>> => {
    return api.get('/dashboard/prodi', { params: { faculty_id: facultyId } });
  },

  getManagementStats: (): Promise<AxiosResponse<any>> => {
    return api.get('/dashboard/management');
  },

  getGradeDistribution: (courseId?: string): Promise<AxiosResponse<any>> => {
    const params = courseId ? { course_id: courseId } : {};
    return api.get('/dashboard/grade-distribution', { params });
  },

  getEnrollmentTrends: (params?: { period?: string; faculty_id?: string; major_id?: string }): Promise<AxiosResponse<any>> => {
    return api.get('/dashboard/enrollment-trends', { params });
  },

  getDosenStats: (instructorName: string): Promise<AxiosResponse<any>> => {
    return api.get(`/dashboard/dosen/${instructorName}`);
  },

  getFacultyEnrollment: (): Promise<AxiosResponse<any>> => {
    return api.get('/dashboard/faculty-enrollment');
  }
};

// ============================================================================
// PAYMENT API
// ============================================================================

export const paymentAPI = {
  createTransaction: (order_id: string, amount: number): Promise<AxiosResponse<{ snap_token: string }>> => {
    return api.post('/payment/create-transaction', { order_id, amount });
  },

  checkTransactionStatus: (order_id: string): Promise<AxiosResponse<any>> => {
    return api.get(`/payment/status/${order_id}`);
  }
};

// ============================================================================
// ENROLLMENT API
// ============================================================================

export const enrollmentAPI = {
  getAll: (): Promise<AxiosResponse<any[]>> => {
    return api.get('/enrollments');
  },

  getById: (id: string): Promise<AxiosResponse<any>> => {
    return api.get(`/enrollments/${id}`);
  },

  getByCourse: (courseId: string): Promise<AxiosResponse<any[]>> => {
    return api.get(`/enrollments/course/${courseId}`);
  },

  approve: (id: string): Promise<AxiosResponse<any>> => {
    return api.put(`/enrollments/${id}/approve`);
  },

  reject: (id: string): Promise<AxiosResponse<any>> => {
    return api.put(`/enrollments/${id}/reject`);
  },

  delete: (id: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/enrollments/${id}`);
  }
};

// ============================================================================
// STUDENT API
// ============================================================================

export const studentAPI = {
  getMyCourses: (): Promise<AxiosResponse<Course[]>> => {
    return api.get('/student/my-courses').then(response => ({
      ...response,
      data: response.data?.data || response.data || []
    }));
  },

  getMyAssignments: (): Promise<AxiosResponse<Assignment[]>> => {
    return api.get('/student/my-assignments').then(response => ({
      ...response,
      data: (response.data?.data || response.data || []).map(transformAssignment)
    }));
  },

  getMyGrades: (): Promise<AxiosResponse<Grade[]>> => {
    return api.get('/student/my-grades');
  },

  getAllCoursesWithProgress: (): Promise<AxiosResponse<Course[]>> => {
    return api.get('/student/all-courses').then(response => ({
      ...response,
      data: response.data?.data || response.data || []
    }));
  }
};

// ============================================================================
// REGISTRATION API
// ============================================================================

export const registrationAPI = {
  getMyRegistration: (): Promise<AxiosResponse<any>> => {
    return api.get('/registrations/my-registration');
  },

  saveRegistration: (data: any): Promise<AxiosResponse<any>> => {
    return api.post('/registrations/save', data);
  },

  getAllRegistrations: (params?: { status?: string; first_choice_id?: string; search?: string; per_page?: number }): Promise<AxiosResponse<any>> => {
    return api.get('/registrations', { params });
  },

  getRegistrationById: (id: string): Promise<AxiosResponse<any>> => {
    return api.get(`/registrations/${id}`);
  },

  reviewRegistration: (id: string, status: 'accepted' | 'rejected', rejectionReason?: string): Promise<AxiosResponse<any>> => {
    return api.post(`/registrations/${id}/review`, {
      status,
      rejection_reason: rejectionReason
    });
  }
};

// ============================================================================
// MANAGEMENT API
// ============================================================================

export const managementAPI = {
  getDashboard: (): Promise<AxiosResponse<any>> => {
    return api.get('/management/dashboard');
  },

  getDashboardData: (): Promise<AxiosResponse<any>> => {
    return api.get('/management/dashboard/data');
  },

  getUsers: (): Promise<AxiosResponse<any>> => {
    return api.get('/management/users');
  },

  getCourses: (): Promise<AxiosResponse<any>> => {
    return api.get('/management/courses');
  },

  getEnrollments: (): Promise<AxiosResponse<any>> => {
    return api.get('/management/enrollments');
  },

  getAnalytics: (): Promise<AxiosResponse<any>> => {
    return api.get('/management/analytics');
  },

  getExport: (): Promise<AxiosResponse<any>> => {
    return api.get('/management/export');
  },

  getRegistrations: (params?: { status?: string; first_choice_id?: string; search?: string; per_page?: number }): Promise<AxiosResponse<any>> => {
    return api.get('/management/registrations', { params });
  },

  getRegistrationById: (id: string): Promise<AxiosResponse<any>> => {
    return api.get(`/management/registrations/${id}`);
  },

  reviewRegistration: (id: string, status: 'accepted' | 'rejected', rejectionReason?: string): Promise<AxiosResponse<any>> => {
    return api.post(`/management/registrations/${id}/review`, {
      status,
      rejection_reason: rejectionReason
    });
  },

  // ==========================================================================
  // MANAGEMENT ADMINISTRATION API
  // ==========================================================================

  getAdministrationOverview: (): Promise<AxiosResponse<any>> => {
    return api.get('/management/administration/overview');
  },

  getRecentPayments: (limit: number = 10): Promise<AxiosResponse<any>> => {
    return api.get('/management/administration/recent-payments', { params: { limit } });
  },

  getPaymentTypes: (): Promise<AxiosResponse<any>> => {
    return api.get('/management/administration/payment-types');
  },

  getPaymentMethods: (): Promise<AxiosResponse<any>> => {
    return api.get('/management/administration/payment-methods');
  },

  getStudentsPaymentStatus: (params?: { search?: string }): Promise<AxiosResponse<any>> => {
    return api.get('/management/administration/students', { params });
  },

  getStudentPaymentDetails: (studentId: string): Promise<AxiosResponse<any>> => {
    return api.get(`/management/administration/students/${studentId}`);
  },

  updatePaymentStatus: (studentId: string, paymentItemId: number, status: string): Promise<AxiosResponse<any>> => {
    return api.put(`/management/administration/students/${studentId}/payments/${paymentItemId}`, { status });
  },

  getFeeTypes: (): Promise<AxiosResponse<any>> => {
    return api.get('/management/administration/fee-types');
  },

  createFeeType: (data: { item_id: string; title_key: string; description_key: string; amount: number }): Promise<AxiosResponse<any>> => {
    return api.post('/management/administration/fee-types', data);
  },

  updateFeeType: (itemId: string, data: { title_key: string; description_key: string; amount: number }): Promise<AxiosResponse<any>> => {
    return api.put(`/management/administration/fee-types/${itemId}`, data);
  },

  deleteFeeType: (itemId: string): Promise<AxiosResponse<void>> => {
    return api.delete(`/management/administration/fee-types/${itemId}`);
  },

  getReceipt: (historyId: string): Promise<AxiosResponse<any>> => {
    return api.get(`/management/administration/receipt/${historyId}`);
  }
};

// ============================================================================
// PRODI API
// ============================================================================

export const prodiAPI = {
  getDashboard: (): Promise<AxiosResponse<any>> => {
    return api.get('/prodi/dashboard');
  },

  getCourses: (): Promise<AxiosResponse<any>> => {
    return api.get('/prodi/courses');
  },

  createCourse: (courseData: Partial<Course>): Promise<AxiosResponse<Course>> => {
    return api.post('/prodi/courses', courseData);
  },

  getLecturers: (): Promise<AxiosResponse<any>> => {
    return api.get('/prodi/lecturers/list');
  },

  getStudents: (): Promise<AxiosResponse<any>> => {
    return api.get('/prodi/students/list');
  },

  getEnrollments: (): Promise<AxiosResponse<any>> => {
    return api.get('/prodi/enrollments');
  },

  getAnalytics: (): Promise<AxiosResponse<any>> => {
    return api.get('/prodi/analytics');
  }
};

// Default export for convenience
export default api;

// ============================================================================
// UNIFIED API SERVICE EXPORT
// Combines all API exports into a single service object
// ============================================================================

export const apiService = {
  // Authentication
  login: authAPI.login,
  logout: authAPI.logout,
  register: authAPI.register,
  forgotPassword: authAPI.forgotPassword,
  resetPassword: authAPI.resetPassword,
  getProfile: authAPI.getProfile,
  getCurrentUser: authAPI.getCurrentUser,

  // Users
  getUsers: userAPI.getAll,
  getUser: userAPI.getById,
  updateUser: userAPI.update,
  createUser: userAPI.create,
  deleteUser: userAPI.delete,
  getFacultyList: userAPI.getFacultyList,
  getStudents: userAPI.getStudents,

  // Courses
  getCourses: courseAPI.getAll,
  getCourse: courseAPI.getById,
  getPublicCourses: courseAPI.getPublic,
  getMyCourses: courseAPI.getMyCourses,
  createCourse: courseAPI.create,
  updateCourse: courseAPI.update,
  deleteCourse: courseAPI.delete,

  // Assignments
  getAssignments: assignmentAPI.getAll,
  getAssignment: assignmentAPI.getById,
  createAssignment: assignmentAPI.create,
  updateAssignment: assignmentAPI.update,
  deleteAssignment: assignmentAPI.delete,

  // Announcements
  getAnnouncements: announcementAPI.getAll,
  getAnnouncement: announcementAPI.getById,
  createAnnouncement: announcementAPI.create,
  updateAnnouncement: announcementAPI.update,
  deleteAnnouncement: announcementAPI.delete,

  // Library Resources
  getLibraryResources: libraryResourceAPI.getAll,
  getLibraryResource: libraryResourceAPI.getById,
  createLibraryResource: libraryResourceAPI.create,
  updateLibraryResource: libraryResourceAPI.update,
  deleteLibraryResource: libraryResourceAPI.delete,

  // Notifications
  getNotifications: notificationAPI.getAll,
  getNotification: notificationAPI.getById,
  markNotificationAsRead: notificationAPI.markRead,
  markAllNotificationsAsRead: notificationAPI.markAllRead,

  // Calendar Events
  getAcademicCalendarEvents: calendarEventAPI.getAll,
  getCalendarEvent: calendarEventAPI.getById,
  createCalendarEvent: calendarEventAPI.create,
  updateCalendarEvent: calendarEventAPI.update,
  deleteCalendarEvent: calendarEventAPI.delete,

  // Grades
  getGrades: gradeAPI.getAll,
  getGrade: gradeAPI.getById,
  getMyGrades: gradeAPI.getMyGrades,
  createGrade: gradeAPI.create,
  updateGrade: gradeAPI.update,
  deleteGrade: gradeAPI.delete,

  // Discussion Threads
  getDiscussionThreads: discussionThreadAPI.getAll,
  getDiscussionThread: discussionThreadAPI.getById,
  createDiscussionThread: discussionThreadAPI.create,
  updateDiscussionThread: discussionThreadAPI.update,
  deleteDiscussionThread: discussionThreadAPI.delete,

  // Discussion Posts
  createDiscussionPost: discussionPostAPI.createPost,
  getDiscussionPost: discussionPostAPI.getById,
  updateDiscussionPost: discussionPostAPI.update,
  deleteDiscussionPost: discussionPostAPI.delete,

  // Dashboard
  getDashboardStats: dashboardAPI.getStats,
  getStudentDashboard: dashboardAPI.getStudentStats,
  getFacultyDashboard: dashboardAPI.getFacultyStats,
  getDosenStats: dashboardAPI.getDosenStats,

  // Management
  getManagementDashboard: managementAPI.getDashboard,
  getManagementDashboardData: managementAPI.getDashboardData,
  getManagementUsers: managementAPI.getUsers,
  getManagementCourses: managementAPI.getCourses,
  getManagementEnrollments: managementAPI.getEnrollments,
  getManagementAnalytics: managementAPI.getAnalytics,

  // Prodi
  getProdiDashboard: prodiAPI.getDashboard,
  getProdiCourses: prodiAPI.getCourses,
  createProdiCourse: prodiAPI.createCourse,
  getProdiLecturers: prodiAPI.getLecturers,
  getProdiStudents: prodiAPI.getStudents,
  getProdiEnrollments: prodiAPI.getEnrollments,
  getProdiAnalytics: prodiAPI.getAnalytics,

  // Payment
  createPaymentTransaction: paymentAPI.createTransaction,
  checkPaymentStatus: paymentAPI.checkTransactionStatus,

  // Enrollment
  getEnrollments: enrollmentAPI.getAll,
  getEnrollment: enrollmentAPI.getById,
  approveEnrollment: enrollmentAPI.approve,
  rejectEnrollment: enrollmentAPI.reject,
  deleteEnrollment: enrollmentAPI.delete,

  // Student
  getStudentCourses: studentAPI.getMyCourses,
  getStudentAssignments: studentAPI.getMyAssignments,
  getStudentGrades: studentAPI.getMyGrades
};
