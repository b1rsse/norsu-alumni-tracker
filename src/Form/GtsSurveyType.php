<?php

namespace App\Form;

use App\Entity\GtsSurvey;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GtsSurveyType extends AbstractType
{
    public function __construct(private Security $security)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $inputAttr   = ['class' => 'form-input'];
        $selectAttr  = ['class' => 'form-select'];
        $canManageCollections = $this->security->isGranted('ROLE_ADMIN');

        // ═══════════════════════════════════════════════════
        //  A. GENERAL INFORMATION
        // ═══════════════════════════════════════════════════

        $builder
            ->add('name', TextType::class, [
                'label' => '1. Name',
                'attr' => array_merge($inputAttr, ['placeholder' => 'Last Name, First Name, Middle Name']),
            ])
            ->add('permanentAddress', TextareaType::class, [
                'required' => false,
                'label' => '2. Permanent Address',
                'attr' => array_merge($inputAttr, ['rows' => 2]),
            ])
            ->add('emailAddress', TextType::class, [
                'required' => false,
                'label' => '3. E-mail Address',
                'attr' => $inputAttr,
            ])
            ->add('telephoneNumber', TextType::class, [
                'required' => false,
                'label' => '4. Telephone / Contact Number(s)',
                'attr' => $inputAttr,
            ])
            ->add('mobileNumber', TextType::class, [
                'required' => false,
                'label' => '5. Mobile Number',
                'attr' => $inputAttr,
            ])
            ->add('civilStatus', ChoiceType::class, [
                'required' => false,
                'label' => '6. Civil Status',
                'placeholder' => '— Select —',
                'attr' => $selectAttr,
                'choices' => [
                    'Single' => 'Single',
                    'Married' => 'Married',
                    'Separated / Divorced' => 'Separated/Divorced',
                    'Married but not living with spouse' => 'Married not living with spouse',
                    'Single Parent' => 'Single Parent',
                    'Widow or Widower' => 'Widow/Widower',
                ],
            ])
            ->add('sex', ChoiceType::class, [
                'required' => false,
                'label' => '7. Sex',
                'placeholder' => '— Select —',
                'attr' => $selectAttr,
                'choices' => ['Male' => 'Male', 'Female' => 'Female'],
            ])
            ->add('birthday', DateType::class, [
                'required' => false,
                'label' => '8. Birthday',
                'widget' => 'single_text',
                'attr' => $inputAttr,
            ])
            ->add('regionOfOrigin', ChoiceType::class, [
                'required' => false,
                'label' => '9. Region of Origin',
                'placeholder' => '— Select —',
                'attr' => $selectAttr,
                'choices' => [
                    'Region I' => 'Region I',
                    'Region II' => 'Region II',
                    'Region III' => 'Region III',
                    'Region IV' => 'Region IV',
                    'Region V' => 'Region V',
                    'Region VI' => 'Region VI',
                    'Region VII' => 'Region VII',
                    'Region VIII' => 'Region VIII',
                    'Region IX' => 'Region IX',
                    'Region X' => 'Region X',
                    'Region XI' => 'Region XI',
                    'Region XII' => 'Region XII',
                    'NCR' => 'NCR',
                    'CAR' => 'CAR',
                    'ARMM' => 'ARMM',
                    'CARAGA' => 'CARAGA',
                ],
            ])
            ->add('province', TextType::class, [
                'required' => false,
                'label' => '10. Province',
                'attr' => $inputAttr,
            ])
            ->add('locationOfResidence', ChoiceType::class, [
                'required' => false,
                'label' => '11. Location of Residence',
                'placeholder' => '— Select —',
                'attr' => $selectAttr,
                'choices' => ['City' => 'City', 'Municipality' => 'Municipality'],
            ]);

        // ═══════════════════════════════════════════════════
        //  B. EDUCATIONAL BACKGROUND
        // ═══════════════════════════════════════════════════

        // Q12 uses a nested CollectionType rendered as dynamic rows in Twig.
        $builder->add('degrees', CollectionType::class, [
            'mapped' => false,
            'required' => false,
            'label' => false,
            'entry_type' => DegreeEntryType::class,
            'entry_options' => [
                'label' => false,
            ],
            'allow_add' => $canManageCollections,
            'allow_delete' => $canManageCollections,
            'prototype' => true,
        ]);

        // Q13 remains handled as dynamic rows in the template (JSON fields).

        // Q14 – Reasons for taking the course (Undergraduate)
        $courseReasons = [
            'High grades in the course or subject area(s) related to the course' => 'High grades',
            'Good grades in high school' => 'Good grades in HS',
            'Influence of parents or relatives' => 'Influence of parents',
            'Peer influence' => 'Peer influence',
            'Inspired by a role model' => 'Role model',
            'Strong passion for the profession' => 'Strong passion',
            'Prospect for immediate employment' => 'Immediate employment',
            'Status or prestige of the profession' => 'Prestige',
            'Availability of course offering in chosen institution' => 'Availability of course',
            'Prospect of career advancement' => 'Career advancement',
            'Affordable for the family' => 'Affordable',
            'Prospect of attractive compensation' => 'Attractive compensation',
            'Opportunity for employment abroad' => 'Employment abroad',
            'No particular choice or no better idea' => 'No particular choice',
        ];

        $builder
            ->add('reasonsForCourseUndergrad', ChoiceType::class, [
                'required' => false,
                'label' => '14. Reasons for taking the course — Undergraduate',
                'choices' => $courseReasons,
                'expanded' => true,
                'multiple' => true,
                'label_attr' => ['class' => 'font-normal text-sm'],
            ])
            ->add('reasonsForCourseGrad', ChoiceType::class, [
                'required' => false,
                'label' => 'Reasons — Graduate / MS / MA / Ph.D.',
                'choices' => $courseReasons,
                'expanded' => true,
                'multiple' => true,
                'label_attr' => ['class' => 'font-normal text-sm'],
            ])
            ->add('reasonsForCourseOther', TextType::class, [
                'required' => false,
                'label' => 'Others (please specify)',
                'attr' => $inputAttr,
            ]);

        // ═══════════════════════════════════════════════════
        //  C. TRAININGS / ADVANCE STUDIES
        // ═══════════════════════════════════════════════════

        // Q15a handled as dynamic rows in template (JSON)

        $builder
            ->add('reasonsAdvanceStudy', ChoiceType::class, [
                'required' => false,
                'label' => '15b. What made you pursue advance studies?',
                'choices' => [
                    'For promotion' => 'For promotion',
                    'For professional development' => 'For professional development',
                ],
                'expanded' => true,
                'multiple' => true,
                'label_attr' => ['class' => 'font-normal text-sm'],
            ])
            ->add('reasonAdvanceStudyOther', TextType::class, [
                'required' => false,
                'label' => 'Others (please specify)',
                'attr' => $inputAttr,
            ])
            ->add('certs', CollectionType::class, [
                // Demonstrates a nested dynamic CollectionType field in Symfony forms.
                // Unmapped for now because GtsSurvey entity has no dedicated certifications property yet.
                'mapped' => false,
                'required' => false,
                'label' => false,
                'entry_type' => TextType::class,
                'entry_options' => [
                    'label' => false,
                    'attr' => array_merge($inputAttr, [
                        'placeholder' => 'Certification (e.g., PRC License, NC II, AWS CCP)',
                    ]),
                ],
                'allow_add' => $canManageCollections,
                'allow_delete' => $canManageCollections,
                'prototype' => true,
            ]);

        // ═══════════════════════════════════════════════════
        //  D. EMPLOYMENT DATA
        // ═══════════════════════════════════════════════════

        $builder
            ->add('presentlyEmployed', ChoiceType::class, [
                'required' => false,
                'label' => '16. Are you presently employed?',
                'placeholder' => '— Select —',
                'attr' => $selectAttr,
                'choices' => [
                    'Yes' => 'Yes',
                    'No' => 'No',
                    'Never Employed' => 'Never Employed',
                ],
            ])
            ->add('reasonsNotEmployed', ChoiceType::class, [
                'required' => false,
                'label' => '17. Reason(s) why you are not yet employed',
                'choices' => [
                    'Advance or further study' => 'Advance study',
                    'Family concern and decided not to find a job' => 'Family concern',
                    'Health-related reason(s)' => 'Health-related',
                    'Lack of work experience' => 'Lack of experience',
                    'No job opportunity' => 'No opportunity',
                    'Did not look for a job' => 'Did not look',
                ],
                'expanded' => true,
                'multiple' => true,
                'label_attr' => ['class' => 'font-normal text-sm'],
            ])
            ->add('reasonNotEmployedOther', TextType::class, [
                'required' => false,
                'label' => 'Other reason(s)',
                'attr' => $inputAttr,
            ])
            ->add('presentEmploymentStatus', ChoiceType::class, [
                'required' => false,
                'label' => '18. Present Employment Status',
                'placeholder' => '— Select —',
                'attr' => $selectAttr,
                'choices' => [
                    'Regular or Permanent' => 'Regular/Permanent',
                    'Temporary' => 'Temporary',
                    'Casual' => 'Casual',
                    'Contractual' => 'Contractual',
                    'Self-employed' => 'Self-employed',
                ],
            ])
            ->add('presentOccupation', ChoiceType::class, [
                'required' => false,
                'label' => '19. Present Occupation (PSOC Classification)',
                'placeholder' => '— Select —',
                'attr' => $selectAttr,
                'choices' => [
                    'Officials of Government & Corporate Executives, Managers' => 'Officials/Executives/Managers',
                    'Professionals' => 'Professionals',
                    'Technicians and Associate Professionals' => 'Technicians',
                    'Clerks' => 'Clerks',
                    'Service Workers and Shop & Market Sales Workers' => 'Service/Sales Workers',
                    'Farmers, Forestry Workers and Fishermen' => 'Farmers/Forestry/Fishermen',
                    'Trades and Related Workers' => 'Trades Workers',
                    'Plant and Machine Operators and Assemblers' => 'Machine Operators',
                    'Laborers and Unskilled Workers' => 'Laborers/Unskilled',
                    'Special Occupation' => 'Special Occupation',
                ],
            ])
            ->add('companyNameAddress', TextareaType::class, [
                'required' => false,
                'label' => '20a. Name of Company / Organization (including address)',
                'attr' => array_merge($inputAttr, ['rows' => 2]),
            ])
            ->add('lineOfBusiness', ChoiceType::class, [
                'required' => false,
                'label' => '20b. Major Line of Business',
                'placeholder' => '— Select —',
                'attr' => $selectAttr,
                'choices' => [
                    'Agriculture, Hunting and Forestry' => 'Agriculture',
                    'Fishing' => 'Fishing',
                    'Mining and Quarrying' => 'Mining',
                    'Manufacturing' => 'Manufacturing',
                    'Electricity, Gas and Water Supply' => 'Electricity/Gas/Water',
                    'Construction' => 'Construction',
                    'Wholesale and Retail Trade' => 'Wholesale/Retail Trade',
                    'Hotels and Restaurants' => 'Hotels/Restaurants',
                    'Transport, Storage and Communication' => 'Transport/Storage/Communication',
                    'Financial Intermediation' => 'Financial Intermediation',
                    'Real Estate, Renting and Business Activities' => 'Real Estate/Business',
                    'Public Administration and Defense' => 'Public Administration',
                    'Education' => 'Education',
                    'Health and Social Work' => 'Health/Social Work',
                    'Other Community, Social and Personal Service Activities' => 'Other Community/Social',
                    'Private Households with Employed Persons' => 'Private Households',
                    'Extra-territorial Organizations and Bodies' => 'Extra-territorial',
                ],
            ])
            ->add('placeOfWork', ChoiceType::class, [
                'required' => false,
                'label' => '21. Place of Work',
                'placeholder' => '— Select —',
                'attr' => $selectAttr,
                'choices' => ['Local' => 'Local', 'Abroad' => 'Abroad'],
            ])
            ->add('isFirstJobAfterCollege', ChoiceType::class, [
                'required' => false,
                'label' => '22. Is this your first job after college?',
                'placeholder' => '— Select —',
                'attr' => $selectAttr,
                'choices' => ['Yes' => true, 'No' => false],
            ])
            ->add('reasonsForStaying', ChoiceType::class, [
                'required' => false,
                'label' => '23. Reason(s) for staying on the job',
                'choices' => [
                    'Salaries and benefits' => 'Salaries/benefits',
                    'Career challenge' => 'Career challenge',
                    'Related to special skill' => 'Related to skill',
                    'Related to course or program of study' => 'Related to course',
                    'Proximity to residence' => 'Proximity',
                    'Peer influence' => 'Peer influence',
                    'Family influence' => 'Family influence',
                ],
                'expanded' => true,
                'multiple' => true,
                'label_attr' => ['class' => 'font-normal text-sm'],
            ])
            ->add('reasonForStayingOther', TextType::class, [
                'required' => false,
                'label' => 'Other reason(s)',
                'attr' => $inputAttr,
            ])
            ->add('firstJobRelatedToCourse', ChoiceType::class, [
                'required' => false,
                'label' => '24. Is your first job related to the course you took up in college?',
                'placeholder' => '— Select —',
                'attr' => $selectAttr,
                'choices' => ['Yes' => true, 'No' => false],
            ])
            ->add('reasonsForAccepting', ChoiceType::class, [
                'required' => false,
                'label' => '25. Reason(s) for accepting the job',
                'choices' => [
                    'Salaries and benefits' => 'Salaries/benefits',
                    'Career challenge' => 'Career challenge',
                    'Related to special skills' => 'Related to skills',
                    'Proximity to residence' => 'Proximity',
                ],
                'expanded' => true,
                'multiple' => true,
                'label_attr' => ['class' => 'font-normal text-sm'],
            ])
            ->add('reasonForAcceptingOther', TextType::class, [
                'required' => false,
                'label' => 'Other reason(s)',
                'attr' => $inputAttr,
            ])
            ->add('reasonsForChanging', ChoiceType::class, [
                'required' => false,
                'label' => '26. Reason(s) for changing job',
                'choices' => [
                    'Salaries and benefits' => 'Salaries/benefits',
                    'Career challenge' => 'Career challenge',
                    'Related to special skills' => 'Related to skills',
                    'Proximity to residence' => 'Proximity',
                ],
                'expanded' => true,
                'multiple' => true,
                'label_attr' => ['class' => 'font-normal text-sm'],
            ])
            ->add('reasonForChangingOther', TextType::class, [
                'required' => false,
                'label' => 'Other reason(s)',
                'attr' => $inputAttr,
            ])
            ->add('durationFirstJob', ChoiceType::class, [
                'required' => false,
                'label' => '27. How long did you stay in your first job?',
                'placeholder' => '— Select —',
                'attr' => $selectAttr,
                'choices' => [
                    'Less than a month' => 'Less than a month',
                    '1 to 6 months' => '1 to 6 months',
                    '7 to 11 months' => '7 to 11 months',
                    '1 year to less than 2 years' => '1-2 years',
                    '2 years to less than 3 years' => '2-3 years',
                    '3 years to less than 4 years' => '3-4 years',
                ],
            ])
            ->add('durationFirstJobOther', TextType::class, [
                'required' => false,
                'label' => 'Others (please specify)',
                'attr' => $inputAttr,
            ])
            ->add('howFoundFirstJob', ChoiceType::class, [
                'required' => false,
                'label' => '28. How did you find your first job?',
                'choices' => [
                    'Response to an advertisement' => 'Advertisement',
                    'As walk-in applicant' => 'Walk-in',
                    'Recommended by someone' => 'Recommended',
                    'Information from friends' => 'Friends',
                    'Arranged by school\'s job placement officer' => 'Job placement',
                    'Family business' => 'Family business',
                    'Job Fair or PESO' => 'Job Fair/PESO',
                ],
                'expanded' => true,
                'multiple' => true,
                'label_attr' => ['class' => 'font-normal text-sm'],
            ])
            ->add('howFoundFirstJobOther', TextType::class, [
                'required' => false,
                'label' => 'Others (please specify)',
                'attr' => $inputAttr,
            ])
            ->add('timeToLandFirstJob', ChoiceType::class, [
                'required' => false,
                'label' => '29. How long did it take you to land your first job?',
                'placeholder' => '— Select —',
                'attr' => $selectAttr,
                'choices' => [
                    'Less than a month' => 'Less than a month',
                    '1 to 6 months' => '1 to 6 months',
                    '7 to 11 months' => '7 to 11 months',
                    '1 year to less than 2 years' => '1-2 years',
                    '2 years to less than 3 years' => '2-3 years',
                    '3 years to less than 4 years' => '3-4 years',
                ],
            ])
            ->add('timeToLandFirstJobOther', TextType::class, [
                'required' => false,
                'label' => 'Others (please specify)',
                'attr' => $inputAttr,
            ])
            ->add('jobLevelFirstJob', ChoiceType::class, [
                'required' => false,
                'label' => '30.1 Job Level — First Job',
                'placeholder' => '— Select —',
                'attr' => $selectAttr,
                'choices' => [
                    'Rank or Clerical' => 'Rank/Clerical',
                    'Professional, Technical or Supervisory' => 'Professional/Technical/Supervisory',
                    'Managerial or Executive' => 'Managerial/Executive',
                    'Self-employed' => 'Self-employed',
                ],
            ])
            ->add('jobLevelCurrentJob', ChoiceType::class, [
                'required' => false,
                'label' => '30.2 Job Level — Current / Present Job',
                'placeholder' => '— Select —',
                'attr' => $selectAttr,
                'choices' => [
                    'Rank or Clerical' => 'Rank/Clerical',
                    'Professional, Technical or Supervisory' => 'Professional/Technical/Supervisory',
                    'Managerial or Executive' => 'Managerial/Executive',
                    'Self-employed' => 'Self-employed',
                ],
            ])
            ->add('initialMonthlyEarning', ChoiceType::class, [
                'required' => false,
                'label' => '31. Initial Gross Monthly Earning in First Job',
                'placeholder' => '— Select —',
                'attr' => $selectAttr,
                'choices' => [
                    'Below ₱5,000' => 'Below 5000',
                    '₱5,000 to less than ₱10,000' => '5000-10000',
                    '₱10,000 to less than ₱15,000' => '10000-15000',
                    '₱15,000 to less than ₱20,000' => '15000-20000',
                    '₱20,000 to less than ₱25,000' => '20000-25000',
                    '₱25,000 and above' => '25000+',
                ],
            ])
            ->add('curriculumRelevant', ChoiceType::class, [
                'required' => false,
                'label' => '32. Was the curriculum relevant to your first job?',
                'placeholder' => '— Select —',
                'attr' => $selectAttr,
                'choices' => ['Yes' => true, 'No' => false],
            ])
            ->add('competenciesUseful', ChoiceType::class, [
                'required' => false,
                'label' => '33. Competencies learned in college useful in first job',
                'choices' => [
                    'Communication skills' => 'Communication',
                    'Human Relations skills' => 'Human Relations',
                    'Entrepreneurial skills' => 'Entrepreneurial',
                    'Problem-solving skills' => 'Problem-solving',
                    'Critical Thinking skills' => 'Critical Thinking',
                ],
                'expanded' => true,
                'multiple' => true,
                'label_attr' => ['class' => 'font-normal text-sm'],
            ])
            ->add('competenciesUsefulOther', TextType::class, [
                'required' => false,
                'label' => 'Other skills (please specify)',
                'attr' => $inputAttr,
            ])
            ->add('suggestions', TextareaType::class, [
                'required' => false,
                'label' => '34. Suggestions to further improve your course curriculum',
                'attr' => array_merge($inputAttr, ['rows' => 4, 'placeholder' => 'Your suggestions…']),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GtsSurvey::class,
        ]);
    }
}
