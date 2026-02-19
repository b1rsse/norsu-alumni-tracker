<?php

namespace App\Entity;
use App\Entity\WorkHistory;
use App\Repository\AlumniRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AlumniRepository::class)]
class Alumni
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'student_id', type: 'string', length: 100, unique: true)]
    private string $studentId;

    #[ORM\Column(name: 'first_name', type: 'string', length: 255)]
    private string $firstName;

    #[ORM\Column(name: 'last_name', type: 'string', length: 255)]
    private string $lastName;

    #[ORM\Column(name: 'course', type: 'string', length: 100)]
    private string $course;

    #[ORM\Column(name: 'batch_year', type: 'integer')]
    private int $batchYear;

    #[ORM\Column(name: 'current_employment_status', type: 'string', length: 100, nullable: true)]
    private ?string $currentEmploymentStatus = null;

    #[ORM\Column(name: 'email', type: 'string', length: 180, unique: true)]
    private string $email;

    #[ORM\OneToMany(mappedBy: 'alumni', targetEntity: WorkHistory::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $workHistories;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $current_position = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $company_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $salary_range = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $skills = null;

    public function __construct()
    {
        $this->workHistories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudentId(): string
    {
        return $this->studentId;
    }

    public function setStudentId(string $studentId): self
    {
        $this->studentId = $studentId;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getCourse(): string
    {
        return $this->course;
    }

    public function setCourse(string $course): self
    {
        $this->course = $course;

        return $this;
    }

    public function getBatchYear(): int
    {
        return $this->batchYear;
    }

    public function setBatchYear(int $batchYear): self
    {
        $this->batchYear = $batchYear;

        return $this;
    }

    public function getCurrentEmploymentStatus(): ?string
    {
        return $this->currentEmploymentStatus;
    }

    public function setCurrentEmploymentStatus(?string $status): self
    {
        $this->currentEmploymentStatus = $status;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection|WorkHistory[]
     */
    public function getWorkHistories(): Collection
    {
        return $this->workHistories;
    }

    public function addWorkHistory(WorkHistory $workHistory): self
    {
        if (! $this->workHistories->contains($workHistory)) {
            $this->workHistories->add($workHistory);
            $workHistory->setAlumni($this);
        }

        return $this;
    }

    public function removeWorkHistory(WorkHistory $workHistory): self
    {
        if ($this->workHistories->removeElement($workHistory)) {
            if ($workHistory->getAlumni() === $this) {
                $workHistory->setAlumni(null);
            }
        }

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getCurrentPosition(): ?string
    {
        return $this->current_position;
    }

    public function setCurrentPosition(?string $current_position): static
    {
        $this->current_position = $current_position;

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->company_name;
    }

    public function setCompanyName(?string $company_name): static
    {
        $this->company_name = $company_name;

        return $this;
    }

    public function getSalaryRange(): ?string
    {
        return $this->salary_range;
    }

    public function setSalaryRange(?string $salary_range): static
    {
        $this->salary_range = $salary_range;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getSkills(): ?string
    {
        return $this->skills;
    }

    public function setSkills(?string $skills): static
    {
        $this->skills = $skills;

        return $this;
    }
}
