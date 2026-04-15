<?php

namespace App\Form;

use App\Entity\Alumni;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AlumniType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ── Personal Information ──
            ->add('studentNumber', TextType::class, ['label' => 'Student Number', 'attr' => ['class' => 'form-input']])
            ->add('firstName', TextType::class, ['label' => 'First Name', 'attr' => ['class' => 'form-input']])
            ->add('middleName', TextType::class, ['label' => 'Middle Name', 'required' => false, 'attr' => ['class' => 'form-input']])
            ->add('lastName', TextType::class, ['label' => 'Last Name', 'attr' => ['class' => 'form-input']])
            ->add('suffix', ChoiceType::class, [
                'label' => 'Suffix', 'required' => false,
                'placeholder' => '— None —',
                'choices' => ['Jr.' => 'Jr.', 'Sr.' => 'Sr.', 'II' => 'II', 'III' => 'III', 'IV' => 'IV'],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('sex', ChoiceType::class, [
                'label' => 'Sex', 'required' => false,
                'placeholder' => '— Select —',
                'choices' => ['Male' => 'Male', 'Female' => 'Female'],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('dateOfBirth', DateType::class, [
                'label' => 'Date of Birth', 'required' => false,
                'widget' => 'single_text', 'attr' => ['class' => 'form-input'],
            ])
            ->add('civilStatus', ChoiceType::class, [
                'label' => 'Civil Status', 'required' => false,
                'placeholder' => '— Select —',
                'choices' => ['Single' => 'Single', 'Married' => 'Married', 'Widowed' => 'Widowed', 'Separated' => 'Separated'],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('contactNumber', TextType::class, ['label' => 'Contact Number', 'required' => false, 'attr' => ['class' => 'form-input']])
            ->add('emailAddress', TextType::class, ['label' => 'Email Address', 'attr' => ['class' => 'form-input']])
            ->add('homeAddress', TextareaType::class, ['label' => 'Home Address', 'required' => false, 'attr' => ['class' => 'form-input', 'rows' => 2]])
            ->add('province', TextType::class, ['label' => 'Province', 'required' => false, 'attr' => ['class' => 'form-input']])

            // ── Academic Information ──
            ->add('course', ChoiceType::class, [
                'label' => 'Course', 'required' => false,
                'placeholder' => '— Select Course —',
                'choices' => [
                    // College of Arts & Sciences
                    'Bachelor of Arts in English Language (AB English)' => 'AB English',
                    'Bachelor of Arts in Political Science (AB PolSci)' => 'AB PolSci',
                    'Bachelor of Science in Biology (BS Biology)' => 'BS Biology',
                    'Bachelor of Science in Mathematics (BS Math)' => 'BS Math',
                    'Bachelor of Science in Environmental Science (BSES)' => 'BSES',
                    // College of Business Administration
                    'Bachelor of Science in Accountancy (BSA)' => 'BSA',
                    'Bachelor of Science in Business Administration (BSBA)' => 'BSBA',
                    'Bachelor of Science in Hospitality Management (BSHM)' => 'BSHM',
                    'Bachelor of Science in Tourism Management (BSTM)' => 'BSTM',
                    // College of Education
                    'Bachelor of Elementary Education (BEEd)' => 'BEEd',
                    'Bachelor of Secondary Education (BSEd)' => 'BSEd',
                    'Bachelor of Physical Education (BPEd)' => 'BPEd',
                    'Bachelor of Special Needs Education (BSNEd)' => 'BSNEd',
                    // College of Engineering & Architecture
                    'Bachelor of Science in Civil Engineering (BSCE)' => 'BSCE',
                    'Bachelor of Science in Electrical Engineering (BSEE)' => 'BSEE',
                    'Bachelor of Science in Mechanical Engineering (BSME)' => 'BSME',
                    'Bachelor of Science in Architecture (BS Architecture)' => 'BS Architecture',
                    // College of Information Technology
                    'Bachelor of Science in Information Technology (BSIT)' => 'BSIT',
                    'Bachelor of Science in Computer Science (BSCS)' => 'BSCS',
                    'Bachelor of Science in Information Systems (BSIS)' => 'BSIS',
                    // College of Nursing & Allied Health Sciences
                    'Bachelor of Science in Nursing (BSN)' => 'BSN',
                    'Bachelor of Science in Midwifery (BS Midwifery)' => 'BS Midwifery',
                    // College of Agriculture
                    'Bachelor of Science in Agriculture (BSA Agriculture)' => 'BSA Agriculture',
                    'Bachelor of Science in Fisheries (BS Fisheries)' => 'BS Fisheries',
                    // College of Criminal Justice Education
                    'Bachelor of Science in Criminology (BS Criminology)' => 'BS Criminology',
                    // College of Industrial Technology
                    'Bachelor of Science in Industrial Technology (BSIT IndTech)' => 'BSIT IndTech',
                    // Other
                    'Other' => 'Other',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('college', ChoiceType::class, [
                'label' => 'College', 'required' => false,
                'placeholder' => '— Select College —',
                'choices' => [
                    'College of Arts & Sciences (CAS)' => 'CAS',
                    'College of Business Administration (CBA)' => 'CBA',
                    'College of Education (COEd)' => 'COEd',
                    'College of Engineering & Architecture (CEA)' => 'CEA',
                    'College of Information Technology (CIT)' => 'CIT',
                    'College of Nursing & Allied Health Sciences (CNAHS)' => 'CNAHS',
                    'College of Agriculture (CA)' => 'CA',
                    'College of Criminal Justice Education (CCJE)' => 'CCJE',
                    'College of Industrial Technology (CIndTech)' => 'CIndTech',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('yearGraduated', IntegerType::class, ['label' => 'Year Graduated', 'required' => false, 'attr' => ['class' => 'form-input', 'placeholder' => 'e.g. 2024']])
            ->add('honorsReceived', TextType::class, ['label' => 'Honors Received', 'required' => false, 'attr' => ['class' => 'form-input']])
            ->add('degreeProgram', TextType::class, ['label' => 'Degree Program', 'required' => false, 'attr' => ['class' => 'form-input']])
            ->add('major', TextType::class, ['label' => 'Major', 'required' => false, 'attr' => ['class' => 'form-input']])
            ->add('dateGraduated', DateType::class, ['label' => 'Date Graduated', 'required' => false, 'widget' => 'single_text', 'attr' => ['class' => 'form-input']])
            ->add('latinHonor', ChoiceType::class, [
                'label' => 'Latin Honor', 'required' => false,
                'placeholder' => '— None —',
                'choices' => ['Summa Cum Laude' => 'Summa Cum Laude', 'Magna Cum Laude' => 'Magna Cum Laude', 'Cum Laude' => 'Cum Laude'],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('gwa', TextType::class, ['label' => 'GWA', 'required' => false, 'attr' => ['class' => 'form-input']])
            ->add('scholarshipGranted', TextType::class, ['label' => 'Scholarship Granted', 'required' => false, 'attr' => ['class' => 'form-input']])

            // ── Employment Information ──
            ->add('employmentStatus', ChoiceType::class, [
                'label' => 'Employment Status', 'required' => false,
                'placeholder' => '— Select —',
                'choices' => ['Employed' => 'Employed', 'Self-Employed' => 'Self-Employed', 'Unemployed' => 'Unemployed', 'Freelance' => 'Freelance'],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('employmentType', ChoiceType::class, [
                'label' => 'Employment Type', 'required' => false,
                'placeholder' => '— Select —',
                'choices' => ['Full-time' => 'Full-time', 'Part-time' => 'Part-time', 'Contractual' => 'Contractual', 'Casual' => 'Casual'],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('companyName', TextType::class, ['label' => 'Company Name', 'required' => false, 'attr' => ['class' => 'form-input']])
            ->add('jobTitle', TextType::class, ['label' => 'Job Title / Position', 'required' => false, 'attr' => ['class' => 'form-input']])
            ->add('jobLevel', ChoiceType::class, [
                'label' => 'Job Level', 'required' => false,
                'placeholder' => '— Select —',
                'choices' => ['Entry Level' => 'Entry Level', 'Rank/Clerical' => 'Rank/Clerical', 'Professional/Technical' => 'Professional/Technical', 'Supervisory' => 'Supervisory', 'Managerial/Executive' => 'Managerial/Executive'],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('industry', TextType::class, ['label' => 'Industry', 'required' => false, 'attr' => ['class' => 'form-input']])
            ->add('companyAddress', TextareaType::class, ['label' => 'Company Address', 'required' => false, 'attr' => ['class' => 'form-input', 'rows' => 2]])
            ->add('dateHired', DateType::class, ['label' => 'Date Hired', 'required' => false, 'widget' => 'single_text', 'attr' => ['class' => 'form-input']])
            ->add('monthlySalary', ChoiceType::class, [
                'label' => 'Monthly Salary', 'required' => false,
                'placeholder' => '— Select —',
                'choices' => [
                    'Below ₱10,000' => 'Below 10000',
                    '₱10,000 – ₱20,000' => '10000-20000',
                    '₱20,001 – ₱40,000' => '20001-40000',
                    '₱40,001 – ₱60,000' => '40001-60000',
                    '₱60,001 – ₱80,000' => '60001-80000',
                    'Above ₱80,000' => 'Above 80000',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('isFirstJob', CheckboxType::class, ['label' => 'Is this the first job after graduation?', 'required' => false, 'label_attr' => ['class' => 'form-check-label']])
            ->add('yearsInCompany', IntegerType::class, ['label' => 'Years in Company', 'required' => false, 'attr' => ['class' => 'form-input']])
            ->add('workAbroad', CheckboxType::class, ['label' => 'Working abroad?', 'required' => false, 'label_attr' => ['class' => 'form-check-label']])
            ->add('countryOfEmployment', TextType::class, ['label' => 'Country of Employment', 'required' => false, 'attr' => ['class' => 'form-input']])

            // ── Career Tracking ──
            ->add('jobRelatedToCourse', CheckboxType::class, ['label' => 'Job related to course?', 'required' => false, 'label_attr' => ['class' => 'form-check-label']])
            ->add('promotionReceived', CheckboxType::class, ['label' => 'Promotion received?', 'required' => false, 'label_attr' => ['class' => 'form-check-label']])
            ->add('datePromoted', DateType::class, ['label' => 'Date Promoted', 'required' => false, 'widget' => 'single_text', 'attr' => ['class' => 'form-input']])
            ->add('skillsUsedInJob', TextareaType::class, ['label' => 'Skills Used in Job', 'required' => false, 'attr' => ['class' => 'form-input', 'rows' => 2]])
            ->add('trainingsAttended', TextareaType::class, ['label' => 'Trainings Attended', 'required' => false, 'attr' => ['class' => 'form-input', 'rows' => 2]])
            ->add('licensesObtained', TextareaType::class, ['label' => 'Licenses Obtained', 'required' => false, 'attr' => ['class' => 'form-input', 'rows' => 2]])
            ->add('certifications', TextareaType::class, ['label' => 'Certifications', 'required' => false, 'attr' => ['class' => 'form-input', 'rows' => 2]])
            ->add('careerAchievements', TextareaType::class, ['label' => 'Career Achievements', 'required' => false, 'attr' => ['class' => 'form-input', 'rows' => 2]])

            // ── Feedback & University Contribution ──
            ->add('furtherStudies', CheckboxType::class, ['label' => 'Pursuing further studies?', 'required' => false, 'label_attr' => ['class' => 'form-check-label']])
            ->add('postgraduateDegree', TextType::class, ['label' => 'Postgraduate Degree', 'required' => false, 'attr' => ['class' => 'form-input']])
            ->add('schoolForFurtherStudies', TextType::class, ['label' => 'School for Further Studies', 'required' => false, 'attr' => ['class' => 'form-input']])
            ->add('recommendNorsu', CheckboxType::class, ['label' => 'Would recommend NORSU?', 'required' => false, 'label_attr' => ['class' => 'form-check-label']])
            ->add('suggestionsForUniversity', TextareaType::class, ['label' => 'Suggestions for the University', 'required' => false, 'attr' => ['class' => 'form-input', 'rows' => 3]])
            ->add('willingForSeminar', CheckboxType::class, ['label' => 'Willing to be a seminar speaker?', 'required' => false, 'label_attr' => ['class' => 'form-check-label']])
            ->add('willingForDonation', CheckboxType::class, ['label' => 'Willing to donate?', 'required' => false, 'label_attr' => ['class' => 'form-check-label']])
            ->add('willingForMentorship', CheckboxType::class, ['label' => 'Willing to mentor students?', 'required' => false, 'label_attr' => ['class' => 'form-check-label']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Alumni::class,
        ]);
    }
}
