# DMRMS Complete Recruitment Workflow
**A comprehensive end-to-end digital recruitment management system — from cycle creation to final admission decision.**

→ Application Processing
→ Applicant Entry
→ Voucher & Purchase
→ Cycle Setup

The DMRMS workflow ensures every applicant moves through a structured, transparent, and auditable recruitment pipeline — from the moment a cycle is created to the final notification of admission or rejection.

---


## 1. Recruitment Cycle Workflow
The recruitment process begins when an administrator configures and publishes a new recruitment exercise through the system.
*   **Admin Creates Cycle** → Set Application Dates → Publish Exercise → Set Vacancies → Set Eligibility

### Active Recruitment Campaign
Once published, the recruitment exercise becomes live and visible to all prospective applicants through the public portal.

### Public Recruitment Portal
The portal serves as the primary interface for applicants to discover open positions, review eligibility requirements, and begin the application process.

---

## 2. Voucher Purchasing Workflow
1. Visit Portal
2. Buy Voucher
3. Choose Payment
4. Validate Payment

### Business Rules
*   **One Voucher Per Applicant:** Each applicant is permitted to purchase only a single voucher per recruitment cycle to ensure fairness.
*   **Voucher Expiry:** All vouchers are time-bound and expire automatically at the close of the recruitment cycle.
*   **No Reuse Policy:** Once a voucher has been used for registration, it is permanently invalidated and cannot be reused.

---

## 3. Applicant Registration Workflow
1. **Enter Voucher Code:** Applicant inputs the unique voucher code received via SMS or email to begin registration.
2. **System Validates Voucher:** The system verifies the voucher is valid, unused, and within the active recruitment cycle period.
3. **Create Account:** Applicant provides personal details to create a new account in the system.
4. **Verify Email/Phone:** A verification code is sent to confirm the applicant's contact details are valid and accessible.
5. **Create Password & Activate:** Applicant sets a secure password, activating the account and gaining access to the dashboard.

**Outputs:**
*   **Applicant Account:** A fully activated account linked to the applicant's voucher and contact information.
*   **Applicant Profile:** A personal profile ready to be populated with application details, documents, and declarations.

---

## 4. Application Submission Workflow
### Step-by-Step Submission Process
1. **Log In:** Applicant logs into their activated dashboard to begin the application.
2. **Complete Personal Information:** Full name, date of birth, contact details, and residential address.
3. **Complete Educational Information:** Academic qualifications, institutions attended, and certificates obtained.
4. **Complete Physical & Health Information:** Physical measurements, health declarations, and fitness status.
5. **Save Draft → Review → Submit:** Applicant saves progress, reviews all entries for accuracy, then submits the final application.

> **Validation Rules:** All mandatory fields must be completed. Age requirements must be met. Duplicate applications are automatically rejected by the system.

---

## 5. Document Upload Workflow
*   **Birth Certificate:** Official birth certificate confirming the applicant's date of birth and nationality of origin.
*   **Educational Certificate:** Certified copies of all academic qualifications relevant to the position applied for.
*   **National ID:** Valid government-issued national identification card for identity verification purposes.
*   **Passport Photograph:** Recent passport-sized photograph meeting the specified format and background requirements.

### System Validation Checks
*   **File Type:** Only accepted formats (PDF, JPG, PNG) are permitted for upload.
*   **File Size:** Each document must fall within the maximum allowed file size limit.
*   **Completeness:** All required documents must be present before the application can proceed.

**Output: Verified Upload Status**
Upon successful validation, each document receives a verified status tag. Documents are stored securely in the system with encryption and access controls, ensuring data integrity and privacy throughout the recruitment process.

---

## 6. Automated Eligibility Screening Workflow
Once an application is submitted, the system automatically runs a multi-point eligibility check without manual intervention.
*   **Eligible:** Applicant meets all defined criteria and is automatically advanced to the shortlisting pool for administrator review.
*   **Not Eligible:** Applicant is notified of the specific failed criteria and the application is flagged accordingly in the system.

---

## 7. Smart Application Tracking Workflow
### Application Status Timeline
`Registered` → `Application Draft` → `Application Submitted` → `Documents Verified` → `Eligibility Approved` → `Shortlisted` → `Appointment Scheduled` → `Screening Completed` → `Final Decision Pending` → `Selected/Rejected`

### Tracking Features
*   **Progress Bar:** Visual indicator showing how far along the application is in the pipeline.
*   **Multi-Channel Notifications:** Real-time updates delivered via SMS, email, and in-app notifications at every status change.

---

## 8. Shortlisting Workflow
The shortlisting stage bridges automated eligibility screening and human administrative review, ensuring that only qualified candidates advance to the physical screening phase.

**Output: Shortlisted Candidate**
Each approved candidate receives an official shortlisted status in the system, triggering the automatic generation of a unique verification code and a formal notification to the applicant via SMS and email.

### Administrator Controls
*   Review individual applicant profiles and eligibility results
*   Approve or defer candidates based on vacancy availability
*   Trigger batch or individual shortlist notifications
*   Monitor shortlist pool against available vacancies in real time

---

## 9. Verification Code Workflow
### How the Verification Code Is Used
1.  **Screening Entry Verification:** The code is presented at the physical screening venue to confirm the candidate's identity and shortlisted status before entry is granted.
2.  **Appointment Verification:** The code is also used to validate and confirm scheduled appointments, ensuring only authorized shortlisted candidates attend their designated time slots.

> **Security Note:** Each verification code is unique, time-bound, and stored securely in the database. Codes are single-use and tied directly to the individual applicant's profile to prevent fraud or impersonation.

---

## 10. Appointment Scheduling Workflow
The scheduling process begins when an administrator defines the parameters for each appointment slot. Once configured, the system automatically allocates slots and assigns applicants, closing the loop with an automated notification.

### Administrator Defines
*   Venue
*   Date
*   Time
*   Capacity

### Workflow Steps
1. Define Parameters
2. Allocate Slots
3. Assign Applicant
4. Send Notification

**Output:** Appointment Confirmation issued to each assigned applicant upon successful slot allocation and notification delivery.

*This automated pipeline ensures every applicant receives a confirmed appointment with minimal manual intervention, reducing scheduling errors and improving operational efficiency.*

---

## 11. Physical Screening Workflow
Once an applicant arrives at the screening venue, they pass through a rigorous multi-stage process designed to verify identity, assess medical fitness, and evaluate overall suitability. Every result is recorded systematically for downstream processing.

1.  **Applicant Arrives:** Applicant presents at the designated screening venue at the scheduled time.
2.  **Verification Code Validation:** Unique verification code is scanned and validated against the system database.
3.  **Identity Verification:** Official identification documents are cross-checked to confirm applicant identity.
4.  **Medical Examination:** Qualified medical personnel conduct a comprehensive health assessment.
5.  **Fitness Assessment:** Physical fitness tests are administered and scored according to defined standards.
6.  **Interview Assessment:** Structured interview conducted to evaluate communication and suitability.
7.  **Results Recorded:** All assessment outcomes are entered into the system and linked to the applicant profile.

**Output: Screening Result** — a consolidated record of all assessment outcomes stored securely in the recruitment system.

---

## 12. Final Selection Workflow
The final selection stage brings together all screening data for structured evaluation by administrators and an approval committee. Decisions are formally recorded and communicated to applicants through the notification system.

### Workflow Steps
1. Screening Reviewed
2. Administrator Eval
3. Approval Committee
4. Decision Recorded

### Possible Outcomes
Every applicant receives one of three clearly defined outcomes following the committee's deliberation:
*   **Selected:** Applicant meets all criteria and is confirmed for recruitment.
*   **Reserve List:** Applicant qualifies but is held pending available positions.
*   **Rejected:** Applicant does not meet the required standards for selection.

---

## 13. Notification Workflow
The notification system is the communication backbone of the entire recruitment process. Triggered automatically by system events, it delivers timely updates to applicants via multiple channels while keeping the dashboard current.

### Workflow Steps
1. System Trigger/admins initiate
2. Generate Notification and pushed to the desired recievers
3. Send Email (simulate for now)
4. Send SMS (simulate for now)
5. Update Dashboard

Notifications are dispatched across all **seven key recruitment milestones**, ensuring applicants are never left uninformed at any stage of the process:
1.  **Registration:** Welcome message upon account creation.
2.  **Submission:** Confirmation of application receipt.
3.  **Eligibility:** Result of eligibility verification.
4.  **Shortlisting:** Notification of shortlist status.
5.  **Appointment:** Scheduling confirmation details.
6.  **Selection:** Final selection outcome alert.
7.  **Rejection:** Rejection notice with next steps.

### Build on the announcement workflow
### Build on a feed workflow where admins can publish feed on the homepage or other respective pages with not just text only but images, infographics, etc. just like a news platform.
---

## 14. Administrator Workflow
Administrators are the operational core of the recruitment system. From login to report generation, every action is structured within a logical workflow that ensures full oversight of the recruitment cycle and applicant pipeline.

1.  **Login:** Secure authentication into the system.
2.  **Dashboard:** Overview of all active recruitment metrics.
3.  **Manage Recruitment Cycle:** Configure and activate recruitment rounds.
4.  **Monitor Applications:** Track real-time application submissions.
5.  **Review Documents:** Validate uploaded applicant documents.
6.  **Manage Shortlisting:** Apply criteria to filter qualified applicants.
7.  **Manage Scheduling:** Assign appointment slots to shortlisted applicants.
8.  **Generate Reports:** Export analytics and recruitment summaries.
9.  **Manage Users:** Control access roles and user accounts.

---

## 15. Super Administrator Workflow
The Super Administrator holds the highest level of system authority, overseeing platform integrity, security, and operational continuity. This role governs all system-level configurations that underpin the entire recruitment infrastructure.

### Key Responsibilities
*   **System Settings:** Configure global parameters, thresholds, and platform-wide preferences.
*   **Audit Logs:** Review complete activity trails for compliance and accountability.
*   **Backup Management:** Schedule and verify data backups to prevent loss of critical records.
*   **Security Management:** Enforce access controls, monitor threats, and manage authentication policies.

---

## 16. Reporting & Analytics Workflow
Data-driven decision making is central to effective recruitment management. The reporting workflow transforms raw recruitment data into actionable insights through visual dashboards and exportable reports.

### Workflow Steps
1. Collect Data
2. Visual Dashboards
3. Export Reports

### Key Performance Indicators (KPIs)
The following KPIs are tracked and reported across every recruitment cycle to measure effectiveness and identify areas for improvement:

| KPI | Description |
| :--- | :--- |
| **Total Applicants** | Complete count of all registered and submitted applications per cycle. |
| **Eligible Applicants** | Number of applicants who passed the eligibility verification stage. |
| **Rejected Applicants** | Count of applicants who did not meet the required criteria at any stage. |
| **Regional Distribution** | Breakdown of applicants by geographic region for equity analysis. |
| **Gender Distribution** | Proportional representation of applicants by gender across the cycle. |
| **Success Rate** | Percentage of applicants who successfully completed the full recruitment journey. |

---

## 17. Complete End-to-End Workflow
The full recruitment journey spans thirteen interconnected stages — from the initial voucher purchase through to final recruitment completion. Every stage feeds seamlessly into the next, creating a unified and auditable process.

*   **Phase 1: Entry** — Voucher Purchase, Registration, Application, Document Upload
*   **Phase 2: Evaluation** — Eligibility Check, Tracking, Shortlisting, Verification Code
*   **Phase 3: Assessment** — Scheduling, Screening, Final Decision
*   **Phase 4: Closure** — Notification, Recruitment Completion
