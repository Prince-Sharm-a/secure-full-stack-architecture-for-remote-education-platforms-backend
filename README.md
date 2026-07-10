# API Routes Walkthrough (Step by Step)

This document explains how the routing in [routes/api.php](routes/api.php) is organized and how requests flow through middleware and endpoint groups.

## 1) Controller Imports

At the top of [routes/api.php](routes/api.php), the file imports all controllers that handle API actions, for example:

- `AuthController` for authentication and security actions
- `CoursesController`, `ModuleController`, `LessonController` for learning content
- `EnrollmentController` for student enrollments
- `AssignmentController`, `SubmissionController` for assignment lifecycle
- `PaymentController` for order/payment operations
- `AnalyticsController` for dashboards and reports
- `UserController` and `FeatureController` for profile/admin/upload features

The route file itself only maps URLs to controller methods. Business logic is handled inside controllers/services.

## 2) Public Health/Test Endpoint

```php
Route::get('/test', function () {
	return [
		'success' => true,
		'message' => 'Welcome'
	];
});
```

This is a simple public endpoint to verify that the API is reachable.

## 3) Global API Group (`/v1` + delayed response middleware)

Most routes are inside:

```php
Route::middleware(['delay.response'])->prefix('/v1')->group(function () {
	...
});
```

Meaning:

- Every nested route starts with `/v1`
- Every nested route passes through `delay.response` middleware

Example: `/auth/login` becomes `/v1/auth/login`.

## 4) Authentication Routes (with login throttling)

Inside `/v1`, auth routes are wrapped with `throttle:login`:

- `POST /v1/auth/login` -> `AuthController@login`
- `POST /v1/auth/register` -> `AuthController@register`
- `POST /v1/auth/forgot-password` -> `AuthController@forgotPassword`
- `POST /v1/auth/reset-password` -> `AuthController@resetPassword`
- `GET /v1/auth/email-verify/{token}` -> `AuthController@emailVerify`
- `POST /v1/auth/resend-verification` -> `AuthController@resendVerification`

### Protected auth action

Under `auth:sanctum`:

- `GET /v1/auth/logout` -> `AuthController@logout`

### 2FA actions

- `POST /v1/auth/2fa/enable`
- `POST /v1/auth/2fa/verify`
- `POST /v1/auth/2fa/disable`

All map to `AuthController` 2FA methods.

## 5) Authenticated User Profile/Security Account Actions

Group middleware: `auth:sanctum`

- `GET /v1/user/profile`
- `PUT /v1/user/profile`
- `PUT /v1/user/change-password`
- `GET /v1/user/active-devices`
- `DELETE /v1/user/logout-devices/{id}`
- `DELETE /v1/user/delete-account`

Only logged-in users can access these.

## 6) Public Course Discovery Endpoints

No auth middleware in this group:

- `GET /v1/courses`
- `GET /v1/courses/{id}` (`id` constrained to numbers)
- `GET /v1/courses/search`
- `GET /v1/courses/category/{slug}`

This allows browsing/searching courses publicly.

## 7) Teacher/Admin/Dev Content Management

Group middleware:

- `auth:sanctum`
- `role:teacher,admin,dev`
- `account:active`

This enforces authentication, role-based access, and active account status.

### Course management

- `POST /v1/teacher/courses`
- `PUT /v1/teacher/courses/{id}`
- `DELETE /v1/teacher/courses/{id}`
- `GET /v1/teacher/courses`
- `GET /v1/teacher/courses/{id}`
- `GET /v1/teacher/coursesList`

### Module management

- `POST /v1/teacher/modules`
- `PUT /v1/teacher/modules/{id}`
- `DELETE /v1/teacher/modules/{id}`
- `GET /v1/courses/{course_id}/modules`

### Lesson management

- `POST /v1/teacher/lessons`
- `PUT /v1/teacher/lessons/{id}`
- `DELETE /v1/teacher/lessons/{id}`
- `GET /v1/modules/{module_id}/lessons`
- `GET /v1/lessons/{id}`

### Assignment management

- `POST /v1/teacher/assignments`
- `PUT /v1/teacher/assignments/{id}`
- `DELETE /v1/teacher/assignments/{id}`
- `GET /v1/teacher/course/{course_id}/assignments`

### Grading

- `POST /v1/teacher/grade/{submission_id}`
- `GET /v1/teacher/submissions/{assignment_id}`

### Teacher dashboard/analytics

- `GET /v1/teacher/dashboard`
- `GET /v1/teacher/analytics/{course_id}`
- `GET /v1/teacher/revenue-report`

### Teacher student-list view

- `GET /v1/teacher/students/{course_id}`

## 8) Student/Dev/Admin Learning Endpoints

Group middleware:

- `auth:sanctum`
- `role:student,dev,admin`
- `account:active`

Nested middleware: `throttle:api`

### Student course access

- `GET /v1/student/courses`
- `GET /v1/student/courses/{id}`

### Enrollment and progress

- `POST /v1/enroll/{course_id}`
- `GET /v1/student/enrollments`
- `GET /v1/student/enrollments/{course_id}`
- `PUT /v1/student/progress/{lesson_id}`

### Student assignment actions

- `GET /v1/student/course/{course_id}/assignments`
- `POST /v1/student/submissions`
- `GET /v1/student/submissions/{assignment_id}`

### Student dashboard analytics

- `GET /v1/student/dashboard`
- `GET /v1/student/course-progress/{course_id}`
- `GET /v1/student/upcoming-classes`

## 9) Payment Endpoints

Group middleware:

- `auth:sanctum`
- `verified`
- `account:active`

Endpoints:

- `POST /v1/create-order`
- `POST /v1/verify-payment`
- `GET /v1/student/payment-history`
- `POST /v1/payment/webhook`

Note: webhook routes are often kept unauthenticated in many systems; here it is currently inside the authenticated group.

## 10) Security Log Endpoints

Group middleware: `auth:sanctum`

- `GET /v1/security/login-logs`
- `GET /v1/security/access-logs`
- `GET /v1/security/audit-logs`
- `POST /v1/security/report-suspicious`

These endpoints provide user security visibility and incident reporting.

## 11) Admin Endpoints

Group middleware:

- `auth:sanctum`
- `role:admin,dev`
- `account:active`

Endpoints:

- `GET /v1/admin/failed-logins`
- `GET /v1/admin/block-user/{id}`
- `POST /v1/admin/unblock-user/{id}`
- `GET /v1/admin/dashboard`
- `GET /v1/admin/users`
- `PUT /v1/admin/change-role/{id}`
- `PUT /v1/admin/suspend-user/{id}`
- `GET /v1/admin/courses`
- `PUT /v1/admin/approve-course/{id}`
- `DELETE /v1/admin/delete-course/{id}`

## 12) Upload Endpoints

Group middleware: `auth:sanctum`

- `POST /v1/upload/video`
- `POST /v1/upload/document`
- `DELETE /v1/upload/{id}`

These map to `FeatureController` for file upload and cleanup.

## 13) Key Design Pattern Used in This File

The route file uses nested route groups to apply shared middleware and URL prefixes once instead of repeating them for each endpoint. This creates a clear layered access model:

1. API version and global middleware
2. Authentication status checks
3. Role checks
4. Account-status checks
5. Endpoint-specific controller action

This structure improves readability, security consistency, and maintainability.
