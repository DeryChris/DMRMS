<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            ['cat' => 'general', 'q' => 'What is DMRMS?', 'a' => 'DMRMS (Defence Manpower Recruitment Management System) is the official online portal for Ghana Armed Forces recruitment.', 'order' => 1],
            ['cat' => 'general', 'q' => 'Is the recruitment process free?', 'a' => 'Yes, the application is completely free. Report anyone asking for payment to GAF authorities.', 'order' => 2],
            ['cat' => 'eligibility', 'q' => 'What is the age limit?', 'a' => 'Applicants must be between 18 and 35 years old at the time of application.', 'order' => 3],
            ['cat' => 'eligibility', 'q' => 'Can non-Ghanaians apply?', 'a' => 'No, only Ghanaian citizens by birth are eligible to apply.', 'order' => 4],
            ['cat' => 'eligibility', 'q' => 'What are the height requirements?', 'a' => 'Minimum height is 1.68m for males and 1.60m for females.', 'order' => 5],
            ['cat' => 'eligibility', 'q' => 'What educational qualifications are needed?', 'a' => 'At minimum, applicants must have SSCE/WASSCE with passes in core subjects.', 'order' => 6],
            ['cat' => 'application', 'q' => 'How do I apply?', 'a' => 'Create an account on the portal, complete the application form, upload documents, and submit.', 'order' => 7],
            ['cat' => 'application', 'q' => 'Can I edit my application after submission?', 'a' => 'No, once submitted, the application cannot be edited. Review carefully before submitting.', 'order' => 8],
            ['cat' => 'application', 'q' => 'How long does the application take?', 'a' => 'The application form takes approximately 20-30 minutes to complete.', 'order' => 9],
            ['cat' => 'documents', 'q' => 'What documents are required?', 'a' => 'Birth certificate, National ID, WASSCE/SSCE certificate, passport photograph, medical report, and police clearance.', 'order' => 10],
            ['cat' => 'documents', 'q' => 'What file formats are accepted?', 'a' => 'PDF and JPEG formats are accepted. Maximum file size is 2MB per document.', 'order' => 11],
            ['cat' => 'screening', 'q' => 'Where does screening take place?', 'a' => 'Screening is conducted at designated GAF recruitment centers nationwide.', 'order' => 12],
            ['cat' => 'screening', 'q' => 'What does screening involve?', 'a' => 'Screening includes medical examination, fitness test, and oral interview.', 'order' => 13],
            ['cat' => 'results', 'q' => 'How will I know if I am selected?', 'a' => 'Results are published on the portal and notifications are sent via email and SMS.', 'order' => 14],
            ['cat' => 'results', 'q' => 'Can I appeal a rejection?', 'a' => 'Yes, you may submit an appeal through the portal within 14 days of the decision.', 'order' => 15],
        ];

        foreach ($faqs as $data) {
            Faq::create([
                'category' => $data['cat'],
                'question' => $data['q'],
                'answer' => $data['a'],
                'sort_order' => $data['order'],
            ]);
        }
    }
}
