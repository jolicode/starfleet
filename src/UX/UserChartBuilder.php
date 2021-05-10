<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\UX;

use App\Entity\Conference;
use App\Entity\Participation;
use App\Entity\Submit;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class UserChartBuilder
{
    public function __construct(
        private ChartBuilderInterface $chartBuilder,
    ) {
    }

    /**
     * @param array<Submit>        $pastSubmits
     * @param array<Submit>        $futureSubmits
     * @param array<Conference>    $pastConferences
     * @param array<Participation> $futureParticipations
     */
    public function buildUserChart(array $pastSubmits, array $futureSubmits, array $pastConferences, array $futureParticipations): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $chart->setData([
            'labels' => [
                sprintf('%d talks given', \count($pastSubmits)),
                sprintf('%d talks you will give soon', \count($futureSubmits)),
                sprintf('%d conferences you went to', \count($pastConferences)),
                sprintf('%d conferences you will attend soon', \count($futureParticipations)),
            ],
            'datasets' => [
                [
                    'label' => 'Submitted talks',
                    'data' => [\count($pastSubmits), \count($futureSubmits), \count($pastConferences), \count($futureParticipations)],
                    'backgroundColor' => ['#ffc107', '#28a745', '#dc3545', '#007bff'],
                ],
            ],
        ]);

        $chart->setOptions([
            'legend' => [
                'position' => 'left',
                'align' => 'center',
            ],
        ]);

        return $chart;
    }

    /**
     * @param array<Participation> $pendingParticipations
     * @param array<Participation> $acceptedParticipations
     * @param array<Participation> $rejectedParticipations
     * @param array<Participation> $pastParticipations
     */
    public function buildParticipationsChart(array $pendingParticipations, array $acceptedParticipations, array $rejectedParticipations, array $pastParticipations): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $chart->setData([
            'labels' => [
                sprintf('%d Pending Participations', \count($pendingParticipations)),
                sprintf('%d Accepted Participations', \count($acceptedParticipations)),
                sprintf('%d Rejected Participations', \count($rejectedParticipations)),
                sprintf('%d Past Participations', \count($pastParticipations)),
            ],
            'datasets' => [
                [
                    'label' => 'Participations',
                    'data' => [\count($pendingParticipations), \count($acceptedParticipations), \count($rejectedParticipations), \count($pastParticipations)],
                    'backgroundColor' => ['#ffc107', '#28a745', '#dc3545', '#007bff'],
                ],
            ],
        ]);

        $chart->setOptions([
            'legend' => [
                'position' => 'left',
                'align' => 'center',
            ],
        ]);

        return $chart;
    }

    /**
     * @param array<Submit> $pendingSubmits
     * @param array<Submit> $doneSubmits
     * @param array<Submit> $rejectedSubmits
     * @param array<Submit> $futureSubmits
     */
    public function buildSubmitsChart(array $pendingSubmits, array $doneSubmits, array $rejectedSubmits, array $futureSubmits): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $chart->setData([
            'labels' => [
                sprintf('%d Pending Submits', \count($pendingSubmits)),
                sprintf('%d Past Submits', \count($doneSubmits)),
                sprintf('%d Submits rejected', \count($rejectedSubmits)),
                sprintf('%d Submits accepted', \count($futureSubmits)),
            ],
            'datasets' => [
                [
                    'label' => 'Submits',
                    'data' => [\count($pendingSubmits), \count($doneSubmits), \count($rejectedSubmits), \count($futureSubmits)],
                    'backgroundColor' => ['#ffc107', '#007bff', '#dc3545', '#28a745'],
                ],
            ],
        ]);

        $chart->setOptions([
            'legend' => [
                'position' => 'left',
                'align' => 'center',
            ],
        ]);

        return $chart;
    }
}
