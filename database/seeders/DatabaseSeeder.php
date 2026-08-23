<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\Customer;
use App\Models\Medication;
use App\Models\Order;
use App\Models\User;
use App\Enums\AlertChannel;
use App\Enums\AlertStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Usuarios base de prueba
        $admin = User::factory()->create([
            'name'     => 'Administrador Farmacovigilancia',
            'username' => 'admin',
            'email'    => 'admin@farmacia.com',
        ]);

        // 2. Clientes reutilizables
        $customers = Customer::factory(10)->create();

        // 3. Medicamento con Lote Defectuoso Específico (951357)
        $affectedMedication = Medication::factory()->create([
            'name'        => 'Paracetamol 500mg (Afectado)',
            'description' => 'Lote bajo investigación de farmacovigilancia',
            'lot_number'  => '951357',
        ]);

        // 4. Otros Medicamentos con Lotes Aleatorios
        $otherMedications = Medication::factory(5)->create();

        // 5. Órdenes RECIENTES (Últimos 30 días) asociadas al lote 951357
        Order::factory(5)
            ->recent()
            ->recycle($customers)
            ->create()
            ->each(function (Order $order) use ($affectedMedication, $admin) {
                $order->medications()->syncWithoutDetaching([
                    $affectedMedication->id => [
                        'quantity'   => rand(1, 3),
                        'unit_price' => 12500.00,
                    ],
                ]);

                // Generar alerta para probar el flujo de farmacovigilancia
                Alert::firstOrCreate(
                    [
                        'customer_id' => $order->customer_id,
                        'order_id'    => $order->id,
                        'lot_number'  => $affectedMedication->lot_number,
                        'channel'     => AlertChannel::EMAIL,
                    ],
                    [
                        'user_id'      => $admin->id,
                        'status'       => AlertStatus::SENT,
                        'message_body' => 'Notificación de retiro de lote sanitario 951357',
                        'sent_at'      => now(),
                    ]
                );
            });

        // 6. Órdenes ANTIGUAS (Más de 30 días) asociadas al lote 951357
        Order::factory(3)
            ->old()
            ->recycle($customers)
            ->create()
            ->each(function (Order $order) use ($affectedMedication) {
                $order->medications()->syncWithoutDetaching([
                    $affectedMedication->id => [
                        'quantity'   => rand(1, 2),
                        'unit_price' => 12500.00,
                    ],
                ]);
            });

        // 7. Órdenes Variadas con otros medicamentos
        Order::factory(10)
            ->recycle($customers)
            ->create()
            ->each(function (Order $order) use ($otherMedications) {
                $randomMed = $otherMedications->random();
                $order->medications()->syncWithoutDetaching([
                    $randomMed->id => [
                        'quantity'   => rand(1, 5),
                        'unit_price' => rand(5000, 45000),
                    ],
                ]);
            });
    }
}
