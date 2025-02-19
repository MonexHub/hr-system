<?php

namespace App\Traits;

trait PerformanceCalculations
{
    protected static function bootPerformanceCalculations()
    {
        static::saving(function ($appraisal) {
            $appraisal->calculateOverallRating();
        });
    }

    protected function calculateOverallRating()
    {
        // Define weights for each criteria
        $weights = [
            'quality_of_work' => 0.25,    // 25% weight
            'productivity' => 0.20,        // 20% weight
            'job_knowledge' => 0.15,       // 15% weight
            'reliability' => 0.15,         // 15% weight
            'communication' => 0.15,       // 15% weight
            'teamwork' => 0.10,           // 10% weight
        ];

        $weightedSum = 0;
        $totalWeight = 0;

        foreach ($weights as $attribute => $weight) {
            if (!is_null($this->$attribute)) {
                $weightedSum += $this->$attribute * $weight;
                $totalWeight += $weight;
            }
        }

        $this->overall_rating = $totalWeight > 0
            ? round($weightedSum / $totalWeight, 2)
            : null;

        // Set performance category based on overall rating
        $this->performance_category = $this->calculatePerformanceCategory();
    }

    protected function calculatePerformanceCategory(): string
    {
        return match(true) {
            $this->overall_rating >= 4.5 => 'Outstanding',
            $this->overall_rating >= 3.5 => 'Exceeds Expectations',
            $this->overall_rating >= 2.5 => 'Meets Expectations',
            $this->overall_rating >= 1.5 => 'Needs Improvement',
            default => 'Unsatisfactory',
        };
    }

    public function getScoreColor(): string
    {
        return match(true) {
            $this->overall_rating >= 4.5 => 'success',
            $this->overall_rating >= 3.5 => 'info',
            $this->overall_rating >= 2.5 => 'warning',
            default => 'danger',
        };
    }

    public function getPerformanceMetrics(): array
    {
        return [
            'overall_rating' => $this->overall_rating,
            'performance_category' => $this->performance_category,
            'detailed_scores' => [
                'quality_of_work' => $this->quality_of_work,
                'productivity' => $this->productivity,
                'job_knowledge' => $this->job_knowledge,
                'reliability' => $this->reliability,
                'communication' => $this->communication,
                'teamwork' => $this->teamwork,
            ],
            'strengths' => $this->getStrengths(),
            'areas_for_improvement' => $this->getAreasForImprovement(),
        ];
    }

    protected function getStrengths(): array
    {
        $strengths = [];
        foreach ($this->getDetailedScores() as $metric => $score) {
            if ($score >= 4) {
                $strengths[] = $metric;
            }
        }
        return $strengths;
    }

    protected function getAreasForImprovement(): array
    {
        $improvements = [];
        foreach ($this->getDetailedScores() as $metric => $score) {
            if ($score <= 2) {
                $improvements[] = $metric;
            }
        }
        return $improvements;
    }

    protected function getDetailedScores(): array
    {
        return [
            'Quality of Work' => $this->quality_of_work,
            'Productivity' => $this->productivity,
            'Job Knowledge' => $this->job_knowledge,
            'Reliability' => $this->reliability,
            'Communication' => $this->communication,
            'Teamwork' => $this->teamwork,
        ];
    }
}
