<?php

namespace App\Console\Commands;

use App\Services\SimulationService;
use Illuminate\Console\Command;

class SimulateTrafficCommand extends Command
{
    // Commande de generation de trafic utilisateur continu
    protected $signature = 'tsena:simulate {--requests=10 : Nombre de requetes a envoyer} {--delay=500 : Delai entre requetes en millisecondes} {--continuous : Boucle infinie}';

    protected $description = 'Simuler le trafic utilisateur sur la boutique Tsena Kely';

    public function handle(SimulationService $simulation): int
    {
        $continuous = $this->option('continuous');
        $delayMs = (int) $this->option('delay');
        $total = (int) $this->option('requests');

        $this->info('Demarrage de la simulation de trafic...');

        $iteration = 0;
        while ($continuous || $iteration < $total) {
            $iteration++;
            $result = $simulation->simulateTraffic(1);
            $order = $result['results'][0] ?? null;

            if ($order) {
                $status = $order['status'] === 'completed' ? 'SUCCES' : 'ECHEC';
                $this->line("[#{$iteration}] {$order['order_number']} | Client: {$order['customer']} | Statut: {$status} | Latence: {$order['execution_time_ms']}ms | Trace: {$order['trace_id']}");
            }

            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }
        }

        $this->info("Simulation terminee. {$iteration} requetes traitees.");

        return 0;
    }
}
