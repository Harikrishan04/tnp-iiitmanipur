<?php
session_start();
// Check if user is logged in and is a recruiter
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'recruiter') {
//     header('Location: ../login.php');
//     exit;
// }

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];
$user_role = $_SESSION['user_role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Job Event - TNP Portal</title>
    
    <!-- Tailwind CSS -->
    <link href="/tnp@iiitmanipur/assets/css/2.2.19.tailwind.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="/tnp@iiitmanipur/assets/css/font-awesome.min.css" rel="stylesheet">
    
    <!-- Include Dashboard Styles -->
    <?php include '../../includes/styles.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php include '../../includes/sidebar.php'; ?>
        
        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-h-0">
            <!-- Topbar -->
            <?php include '../../includes/topbar.php'; ?>
            
            <!-- Main Content -->
            <main class="flex-1 flex flex-col min-h-0 p-4 md:p-8 w-full">
                <!-- Page Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Post Job Event</h1>
                    <p class="text-gray-600 mt-2">Create and manage job openings for students</p>
                </div>
                
                <!-- Job Posting Form -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <form class="space-y-6">
                        <!-- Basic Job Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Job Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-600 font-medium mb-2">Job Title</label>
                                    <input type="text" class="form-input" placeholder="e.g., Software Engineer" value="Software Engineer">
                                </div>
                                <div>
                                    <label class="block text-gray-600 font-medium mb-2">Job Type</label>
                                    <select class="form-input">
                                        <option value="">Select job type</option>
                                        <option value="fulltime" selected>Full Time</option>
                                        <option value="parttime">Part Time</option>
                                        <option value="internship">Internship</option>
                                        <option value="contract">Contract</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-600 font-medium mb-2">Department</label>
                                    <input type="text" class="form-input" placeholder="e.g., Engineering" value="Engineering">
                                </div>
                                <div>
                                    <label class="block text-gray-600 font-medium mb-2">Experience Level</label>
                                    <select class="form-input">
                                        <option value="">Select experience level</option>
                                        <option value="fresher">Fresher</option>
                                        <option value="1-2years">1-2 years</option>
                                        <option value="2-5years" selected>2-5 years</option>
                                        <option value="5+years">5+ years</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Location and Salary -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Location & Compensation</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-600 font-medium mb-2">Job Location</label>
                                    <input type="text" class="form-input" placeholder="e.g., Bangalore, India" value="Bangalore, India">
                                </div>
                                <div>
                                    <label class="block text-gray-600 font-medium mb-2">Remote Work</label>
                                    <select class="form-input">
                                        <option value="">Select remote option</option>
                                        <option value="onsite">On-site only</option>
                                        <option value="hybrid" selected>Hybrid</option>
                                        <option value="remote">Remote only</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-600 font-medium mb-2">Salary Range (LPA)</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="number" class="form-input" placeholder="Min" value="8">
                                        <input type="number" class="form-input" placeholder="Max" value="15">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-gray-600 font-medium mb-2">Benefits</label>
                                    <input type="text" class="form-input" placeholder="e.g., Health insurance, flexible hours" value="Health insurance, flexible hours, remote work">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Job Description -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Job Description</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-gray-600 font-medium mb-2">Job Summary</label>
                                    <textarea class="form-input" rows="3" placeholder="Brief description of the role">We are looking for a talented Software Engineer to join our development team. You will be responsible for designing, developing, and maintaining software applications.</textarea>
                                </div>
                                <div>
                                    <label class="block text-gray-600 font-medium mb-2">Key Responsibilities</label>
                                    <textarea class="form-input" rows="4" placeholder="List the main responsibilities">• Design and develop software applications
• Collaborate with cross-functional teams
• Write clean, maintainable code
• Participate in code reviews
• Troubleshoot and debug issues</textarea>
                                </div>
                                <div>
                                    <label class="block text-gray-600 font-medium mb-2">Required Skills</label>
                                    <textarea class="form-input" rows="3" placeholder="List required technical skills">• Strong programming skills in Java/Python
• Experience with web technologies (React, Node.js)
• Knowledge of databases (MySQL, MongoDB)
• Understanding of software development lifecycle</textarea>
                                </div>
                                <div>
                                    <label class="block text-gray-600 font-medium mb-2">Preferred Skills</label>
                                    <textarea class="form-input" rows="3" placeholder="List preferred skills (optional)">• Experience with cloud platforms (AWS, Azure)
• Knowledge of DevOps practices
• Experience with microservices architecture
• Familiarity with Agile methodologies</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Requirements -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Requirements</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-600 font-medium mb-2">Education</label>
                                    <select class="form-input">
                                        <option value="">Select education requirement</option>
                                        <option value="btech" selected>B.Tech/B.E</option>
                                        <option value="mtech">M.Tech/M.E</option>
                                        <option value="mca">MCA</option>
                                        <option value="any">Any Graduate</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-600 font-medium mb-2">Minimum CGPA</label>
                                    <input type="number" step="0.1" min="0" max="10" class="form-input" placeholder="e.g., 7.0" value="7.0">
                                </div>
                                <div>
                                    <label class="block text-gray-600 font-medium mb-2">Branches Eligible</label>
                                    <select class="form-input" multiple>
                                        <option value="cse" selected>Computer Science</option>
                                        <option value="ece">Electronics & Communication</option>
                                        <option value="it">Information Technology</option>
                                        <option value="me">Mechanical Engineering</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-600 font-medium mb-2">Batch Year</label>
                                    <select class="form-input">
                                        <option value="">Select batch year</option>
                                        <option value="2024" selected>2024</option>
                                        <option value="2023">2023</option>
                                        <option value="2022">2022</option>
                                        <option value="any">Any Year</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Event Details -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Event Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-600 font-medium mb-2">Application Deadline</label>
                                    <input type="date" class="form-input" value="2024-02-15">
                                </div>
                                <div>
                                    <label class="block text-gray-600 font-medium mb-2">Number of Positions</label>
                                    <input type="number" class="form-input" placeholder="e.g., 5" value="5">
                                </div>
                                <div>
                                    <label class="block text-gray-600 font-medium mb-2">Interview Date</label>
                                    <input type="date" class="form-input" value="2024-02-20">
                                </div>
                                <div>
                                    <label class="block text-gray-600 font-medium mb-2">Interview Mode</label>
                                    <select class="form-input">
                                        <option value="">Select interview mode</option>
                                        <option value="onsite" selected>On-site</option>
                                        <option value="online">Online</option>
                                        <option value="hybrid">Hybrid</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Additional Information</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-gray-600 font-medium mb-2">Selection Process</label>
                                    <textarea class="form-input" rows="3" placeholder="Describe the selection process">1. Resume Screening
2. Online Assessment
3. Technical Interview
4. HR Interview
5. Final Selection</textarea>
                                </div>
                                <div>
                                    <label class="block text-gray-600 font-medium mb-2">Documents Required</label>
                                    <textarea class="form-input" rows="2" placeholder="List required documents">• Updated Resume
• Academic Certificates
• ID Proof
• Passport size photograph</textarea>
                                </div>
                                <div>
                                    <label class="block text-gray-600 font-medium mb-2">Special Instructions</label>
                                    <textarea class="form-input" rows="2" placeholder="Any special instructions for candidates">• Candidates should be available for immediate joining
• Good communication skills required
• Willingness to work in shifts if needed</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-4 pt-6 border-t">
                            <button type="button" class="btn-secondary">Save Draft</button>
                            <button type="submit" class="submit-btn">Submit for Verification</button>
                        </div>
                    </form>
                </div>
            </main>
            
            <!-- Footer -->
            <?php include '../../includes/footer.php'; ?>
        </div>
    </div>
    
    <!-- Include Dashboard Scripts -->
    <?php include '../../includes/scripts.php'; ?>
</body>
</html> 