export interface Customer {
  id: number;
  name: string;
  email: string;
  phone: string | null;
}

export interface MedicationPivot {
  quantity: number;
  unit_price: string;
}

export interface Medication {
  id: number;
  name: string;
  lot_number: string;
  pivot: MedicationPivot;
}

export interface Order {
  id: number;
  purchase_date: string;
  customer_id: number;
  customer?: Customer;
  medications?: Medication[];
  alerts_count: number;
}

export interface AlertSendPayload {
  lot_number: string;
  order_ids: number[];
  channel: 'email' | 'sms' | 'whatsapp';
  message: string;
}