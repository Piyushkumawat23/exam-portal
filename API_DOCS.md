# API Documentation

## 1. Authentication
| Method | Endpoint | Description | Body Params / Notes |
| :--- | :--- | :--- | :--- |
| POST | `/auth/register` | Register new student | `name`, `email`, `password` |
| POST | `/auth/login` | Login user | `email`, `password` |
| GET | `/auth/me` | Get Current Profile | Requires Bearer Token |

## 2. Forms (Admin Only)
| Method | Endpoint | Description | Body Params |
| :--- | :--- | :--- | :--- |
| GET | `/forms` | List all exams | - |
| GET | `/forms/{id}` | Get single exam details | - |
| POST | `/forms` | Create New Exam | `title`, `description`, `price`, `last_date` |
| PUT | `/forms/{id}` | Update Exam | `title`, `description`, `price`, `last_date` |
| DELETE | `/forms/{id}` | Delete Exam | - |
| GET | `/forms/{id}/applicants` | View Applicants list | - |

## 3. Submissions & Payments (Student)
| Method | Endpoint | Description | Body Params |
| :--- | :--- | :--- | :--- |
| POST | `/submissions` | Apply for exam | `form_id`, `data` (JSON string: name, father, mobile) |
| GET | `/user/submissions` | Get My Applications | - |
| POST | `/payments/create-intent` | Start Stripe Payment | `submission_id` |
| POST | `/payments/confirm` | Confirm & Generate PDF | `payment_id`, `submission_id` |
| POST | `/payments/fail` | Mark Payment as Failed | `submission_id` |
| GET | `/payments/receipt/{id}` | Download PDF Receipt | Requires Token (in Header or Query `?token=...`) |