# Student API Endpoints

This directory contains all student-related API endpoints for the TNP Portal.

## Directory Structure

```
student/
├── index.php          # Main router for student endpoints
├── get_by_id.php     # Get student by ID endpoint
├── student_handler.php # Legacy handler (for backward compatibility)
└── README.md         # This documentation
```

## API Endpoints

### 1. Get Student By ID
**Endpoint:** `GET /dataRouting/api/student/get_by_id.php`

**Parameters:**
- `student_id` (required): UUID of the student

**Example:**
```
GET /dataRouting/api/student/get_by_id.php?student_id=550e8400-e29b-41d4-a716-446655440000
```

**Response:**
```json
{
    "success": true,
    "data": {
        "student_id": "550e8400-e29b-41d4-a716-446655440000",
        "roll_no": "ROLL_550e8400-e29b-41d4-a716-446655440000",
        "name": "John Doe",
        "category": "general",
        "date_of_birth": "2000-01-01",
        "gender": "male",
        "blood_group": "A+",
        "phone_number": "9876543210",
        "locality": "Downtown",
        "city": "Mumbai",
        "state": "Maharashtra",
        "country": "India",
        "pincode": "400001",
        "program": "B.Tech Computer Science and Engineering",
        "department": "CSE",
        "current_semester": 3,
        "cpi": 8.50,
        "year_of_admission": 2021,
        "year_of_passing": 2025,
        "placement_interest": "Interested",
        "comments": "Looking for software development roles",
        "personal_details_json": {
            "personal_email": "john.doe@example.com",
            "linkedin_profile": "https://linkedin.com/in/johndoe",
            "github_profile": "https://github.com/johndoe",
            "portfolio_link": "https://johndoe.dev",
            "programming_skills": ["Python", "JavaScript", "React"],
            "area_of_interest": "Web Development",
            "area_of_interest_other": null
        },
        "education_details_json": {
            "jee_year": 2021,
            "jee_mains_rank": 15000,
            "jee_advanced_cleared": true,
            "jee_advanced_rank": 25000,
            "tenth_board": "CBSE",
            "tenth_score": 95.5,
            "tenth_year_of_passing": 2017,
            "tenth_school_name": "ABC School",
            "twelfth_board": "CBSE",
            "twelfth_stream": "Science",
            "twelfth_score": 92.0,
            "twelfth_year_of_passing": 2019,
            "twelfth_school_name": "XYZ College"
        },
        "experiences_json": {
            "internships": [],
            "certificates": [],
            "projects": []
        },
        "additional_details_json": {
            "family_info": {
                "mother_name": "Jane Doe",
                "father_name": "John Doe Sr.",
                "guardian_name": null
            }
        },
        "documents_json": {
            "photo_link": "https://example.com/photo.jpg",
            "tenth_marksheet_link": "https://example.com/tenth.pdf",
            "twelfth_marksheet_link": "https://example.com/twelfth.pdf",
            "jee_main_scorecard_link": "https://example.com/jee_main.pdf",
            "jee_advanced_scorecard_link": "https://example.com/jee_advanced.pdf",
            "internship_certificate_link": null,
            "other_certificate_link": null
        },
        "created_at": "2024-01-15 10:30:00",
        "updated_at": "2024-01-15 10:30:00"
    }
}
```

## Authentication & Authorization

### Authentication
- Requires valid session token in cookies
- Token must not be expired

### Authorization
- **Admin/Coordinator**: Can access any student's data
- **Student**: Can only access their own data (student_id must match user_id)

## Error Responses

### 401 Unauthorized
```json
{
    "error": "Unauthorized",
    "redirect": "/login"
}
```

### 400 Bad Request
```json
{
    "error": "Student ID is required"
}
```
or
```json
{
    "error": "Invalid student ID format"
}
```

### 403 Forbidden
```json
{
    "error": "Access denied"
}
```

### 404 Not Found
```json
{
    "error": "Student not found"
}
```

### 405 Method Not Allowed
```json
{
    "error": "Method not allowed"
}
```

### 500 Internal Server Error
```json
{
    "error": "Database error occurred"
}
```

## Logging

All API operations are logged to `dataRouting/logs/api_YYYY-MM-DD.log` with the following information:

- Timestamp
- Log level (INFO, WARNING, ERROR)
- Operation name
- User ID performing the action
- IP address and user agent
- Request method and URI
- Operation-specific data

### Log Entry Example:
```json
{
    "timestamp": "2024-01-15 10:30:00",
    "level": "INFO",
    "operation": "STUDENT_RETRIEVED",
    "user_id": "550e8400-e29b-41d4-a716-446655440000",
    "ip_address": "192.168.1.100",
    "user_agent": "Mozilla/5.0...",
    "request_method": "GET",
    "request_uri": "/dataRouting/api/student/get_by_id.php?student_id=...",
    "data": {
        "student_id": "550e8400-e29b-41d4-a716-446655440000",
        "student_name": "John Doe",
        "user_id": "550e8400-e29b-41d4-a716-446655440000"
    }
}
```

## Testing

Use the test script `test_student_api.php` in the root directory to test the API endpoints:

```bash
php test_student_api.php
```

## Database Requirements

The API requires the following database components:

1. **Stored Procedure**: `GetStudentById(p_student_id CHAR(36))`
2. **Tables**: `users`, `roles`, `students`
3. **Triggers**: Student auto-initialization trigger

## Security Considerations

1. **Input Validation**: All inputs are validated for format and content
2. **SQL Injection Protection**: Uses prepared statements
3. **Access Control**: Role-based authorization
4. **Logging**: Comprehensive audit trail
5. **Error Handling**: Secure error messages without exposing system details 