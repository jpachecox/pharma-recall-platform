<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Medication;
use App\Models\Order;
use App\Models\User;
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
        // Usuarios base de prueba
        $admin = User::factory()->create([
            'name'     => 'Administrador Farmacovigilancia',
            'username' => 'admin',
            'email'    => 'admin@farmacia.com',
        ]);

        $customers = Customer::factory(10)->create();

        // Medicamento con Lote Defectuoso Específico (951357)
        $affectedMedication = Medication::factory()->create([
            'name'        => 'Paracetamol 500mg (Afectado)',
            'description' => 'Lote bajo investigación de farmacovigilancia',
            'lot_number'  => '951357',
        ]);

        // Otros Medicamentos con Lotes Aleatorios
        $otherMedications = Medication::factory(5)->create();

        // Órdenes RECIENTES (Últimos 30 días) asociadas al lote 951357
        Order::factory(5)->recent()->create()->each(function (Order $order) use ($affectedMedication) {
            $order->medications()->attach($affectedMedication->id, [
                'quantity'   => rand(1, 3),
                'unit_price' => 12500.00,
            ]);
        });

        // Órdenes ANTIGUAS (Más de 30 días) asociadas al lote 951357
        Order::factory(3)->old()->create()->each(function (Order $order) use ($affectedMedication) {
            $order->medications()->attach($affectedMedication->id, [
                'quantity'   => rand(1, 2),
                'unit_price' => 12500.00,
            ]);
        });

        // Órdenes Variadas (dentro y fuera de rango) con otros medicamentos
        Order::factory(10)->create()->each(function (Order $order) use ($otherMedications) {
            $randomMed = $otherMedications->random();
            $order->medications()->attach($randomMed->id, [
                'quantity'   => rand(1, 5),
                'unit_price' => rand(5000, 45000),
            ]);
        });
    }
}
