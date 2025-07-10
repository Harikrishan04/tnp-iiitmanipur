# TNP Portal Dashboard System Documentation

## Overview

The TNP Portal Dashboard System is a comprehensive role-based application management platform designed for IIIT Manipur's Training and Placement Cell. The system follows a hierarchical workflow where different user roles have specific responsibilities and access levels.

## User Roles and Workflow

### 1. Student Role
**Workflow:** Login → Complete Profile → Apply for Jobs → Track Applications

**Features:**
- **Profile Management:** Create and update personal, academic, and professional information
- **Job Application:** Browse available job openings and apply
- **Application Tracking:** Monitor application status and responses
- **Document Upload:** Upload resume and profile picture

**Pages:**
- `student_dashboard.php` - Main dashboard with stats and quick actions
- `student_profile.php` - Profile creation and editing
- `student_apply.php` - Job browsing and application
- `student_applications.php` - Application tracking and history

### 2. Recruiter Role
**Workflow:** Login → Complete Company Profile → Post Job Openings → Review Applications

**Features:**
- **Company Profile:** Create and manage company information
- **Job Posting:** Create, edit, and manage job openings
- **Application Review:** View and manage student applications
- **Event Management:** Schedule and manage recruitment events

**Pages:**
- `recruiter_dashboard.php` - Main dashboard with job and application stats
- `recruiter_profile.php` - Company profile management
- `recruiter_post_event.php` - Job posting and event creation
- `recruiter_applications.php` - Application review and management

### 3. Coordinator Role
**Workflow:** Verify Students → Verify Recruiters → Verify Events → Send for Admin Approval

**Features:**
- **Student Verification:** Review and approve student profiles
- **Recruiter Verification:** Verify company profiles and credentials
- **Event Verification:** Review job postings and events
- **Quality Control:** Ensure all submissions meet requirements

**Pages:**
- `coordinator_dashboard.php` - Main dashboard with verification stats
- `coordinator_verify_student.php` - Student profile verification
- `coordinator_verify_recruiter.php` - Recruiter profile verification
- `coordinator_verify_event.php` - Event and job posting verification

### 4. Admin Role
**Workflow:** Manage Events → Manage Students → Manage Recruiters → System Oversight

**Features:**
- **Event Management:** Approve, reject, or modify events
- **Student Management:** View and manage student data
- **Recruiter Management:** Manage company accounts
- **System Administration:** Overall system oversight and statistics

**Pages:**
- `admin_dashboard.php` - Main dashboard with system statistics
- `admin_manage_events.php` - Event approval and management
- `admin_manage_students.php` - Student data management
- `admin_manage_recruiter.php` - Recruiter account management

## Complete Workflow

### Phase 1: User Registration and Profile Completion
1. **Students/Recruiters Login** → OTP Authentication
2. **Complete Profile** → Fill all required information
3. **Submit for Verification** → Send to coordinator for review

### Phase 2: Verification Process
1. **Coordinator Review** → Verify student and recruiter profiles
2. **Profile Approval** → Approve or reject with feedback
3. **Admin Oversight** → Monitor verification process

### Phase 3: Job Posting and Application
1. **Recruiter Posts Job** → Create job opening with details
2. **Coordinator Verifies** → Review job posting
3. **Admin Approves** → Final approval for job posting
4. **Students Apply** → Browse and apply for approved jobs

### Phase 4: Application Processing
1. **Application Review** → Recruiters review student applications
2. **Status Updates** → Track application progress
3. **Interview Scheduling** → Coordinate interviews
4. **Final Selection** → Complete hiring process

## Technical Architecture

### File Structure
```
Dashboard/
├── index.php                    # Main dashboard router
├── student_dashboard.php        # Student main dashboard
├── student_profile.php          # Student profile management
├── student_apply.php           # Job application interface
├── student_applications.php    # Application tracking
├── recruiter_dashboard.php     # Recruiter main dashboard
├── recruiter_profile.php       # Company profile management
├── recruiter_post_event.php    # Job posting interface
├── recruiter_applications.php  # Application review
├── coordinator_dashboard.php   # Coordinator main dashboard
├── coordinator_verify_student.php    # Student verification
├── coordinator_verify_recruiter.php  # Recruiter verification
├── coordinator_verify_event.php      # Event verification
├── admin_dashboard.php         # Admin main dashboard
├── admin_manage_events.php     # Event management
├── admin_manage_students.php   # Student management
└── admin_manage_recruiter.php  # Recruiter management
```

### Security Features
- **Session Management:** Secure session handling for all users
- **Role-Based Access:** Strict role verification on each page
- **Input Validation:** Form validation and sanitization
- **File Upload Security:** Secure document upload handling

### Database Integration
- **User Profiles:** Comprehensive user and company profiles
- **Application Tracking:** Complete application lifecycle management
- **Event Management:** Job posting and event scheduling
- **Verification System:** Multi-level approval workflow

## Key Features

### Dashboard Statistics
- **Real-time Stats:** Live counters for applications, approvals, etc.
- **Visual Indicators:** Color-coded status badges and progress bars
- **Quick Actions:** Direct access to common functions

### Responsive Design
- **Mobile-Friendly:** Optimized for all device sizes
- **Modern UI:** Clean, professional interface using Tailwind CSS
- **Accessibility:** Screen reader compatible and keyboard navigation

### Search and Filtering
- **Advanced Search:** Multi-criteria job and application search
- **Status Filtering:** Filter by application status, company, date
- **Pagination:** Efficient data loading for large datasets

### Notification System
- **Status Updates:** Real-time application status notifications
- **Email Alerts:** Automated email notifications for important events
- **In-App Messages:** Internal messaging system

## Installation and Setup

### Prerequisites
- PHP 7.3+
- MySQL 5.7+
- Web server (Apache/Nginx)
- Composer for dependencies

### Installation Steps
1. **Clone Repository:** Download the project files
2. **Database Setup:** Import the SQL schema files
3. **Configuration:** Update database connection settings
4. **Dependencies:** Install PHP dependencies via Composer
5. **Permissions:** Set proper file permissions for uploads
6. **Testing:** Verify all functionality works correctly

### Configuration Files
- `dataRouting/config/db.php` - Database connection
- `dataRouting/config/oauth.php` - OAuth settings
- `includes/` - Modular components

## Usage Guidelines

### For Students
1. **Complete Profile:** Fill all required fields accurately
2. **Upload Documents:** Ensure resume and photo meet requirements
3. **Apply Strategically:** Research companies before applying
4. **Track Applications:** Monitor status and respond promptly

### For Recruiters
1. **Company Profile:** Provide accurate company information
2. **Job Descriptions:** Write clear, detailed job postings
3. **Application Review:** Respond to applications promptly
4. **Event Management:** Coordinate with TNP cell for events

### For Coordinators
1. **Thorough Verification:** Review all submissions carefully
2. **Quality Control:** Ensure profiles meet institutional standards
3. **Timely Processing:** Process verifications within 48 hours
4. **Feedback:** Provide constructive feedback for rejections

### For Administrators
1. **System Monitoring:** Monitor overall system performance
2. **Data Management:** Ensure data integrity and security
3. **User Support:** Assist users with technical issues
4. **Reporting:** Generate reports for institutional use

## Maintenance and Updates

### Regular Maintenance
- **Database Optimization:** Regular cleanup and optimization
- **Security Updates:** Keep PHP and dependencies updated
- **Backup Procedures:** Regular database and file backups
- **Performance Monitoring:** Monitor system performance

### Update Procedures
1. **Backup Current System:** Complete backup before updates
2. **Test Environment:** Test updates in staging environment
3. **Gradual Rollout:** Deploy updates during low-traffic periods
4. **User Communication:** Notify users of maintenance windows

## Troubleshooting

### Common Issues
- **Session Timeouts:** Check session configuration
- **Upload Failures:** Verify file permissions and size limits
- **Database Errors:** Check connection settings and table structure
- **Display Issues:** Clear browser cache and check CSS loading

### Support Contacts
- **Technical Support:** IT department contact information
- **TNP Cell:** Training and Placement office details
- **Documentation:** Access to this documentation

## Future Enhancements

### Planned Features
- **Mobile App:** Native mobile application
- **AI Integration:** Smart job matching algorithms
- **Analytics Dashboard:** Advanced reporting and analytics
- **Integration APIs:** Third-party system integrations

### Scalability Considerations
- **Load Balancing:** Handle increased user traffic
- **Database Optimization:** Improve query performance
- **Caching:** Implement application-level caching
- **CDN Integration:** Content delivery network for assets

---

*This documentation is maintained by the TNP Portal Development Team. For questions or support, please contact the IT department.* 