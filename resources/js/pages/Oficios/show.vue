<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

// Importa todos los componentes finalizados
import OficioHeader from './Partials/OficioHeader.vue';
import DetallesEntrada from './Partials/DetallesEntrada.vue';
import DetallesSalida from './Partials/DetallesSalida.vue';
import ListaDocumentos from './Partials/ListaDocumentos.vue';
import AsignacionesCard from './Partials/AsignacionesCard.vue';
import HistorialExpediente from './Partials/HistorialExpediente.vue';

// --- Definición de Tipos Completa ---
interface User { id: number; name: string; }
interface Area { id: number; nombre: string; }
interface OficioRespuesta { id: number; folio_interno: string; }
interface Documento { id: number; nombre_documento: string; rol_documento: 'principal' | 'anexo'; }
interface Permission { user: User; permission_level: 'editor' | 'visualizador'; }
interface OficioEnHistorial { id: number; tipo: 'entrada' | 'salida'; asunto: string; created_at: string; folio_externo: string | null; folio_salida: string | null; recibidoPor: User | null; }



interface Oficio {
  id: number;
  tipo: 'entrada' | 'salida';
  asunto: string;
  descripcion: string | null;
  folio_externo: string | null;
  folio_salida: string | null;
  status: string;
  prioridad: string;
  remitente: string | null;
  destinatario: string | null;
  fecha_recepcion: string | null;
  fecha_limite: string | null;
  recibidoPor: User | null;
  tiene_turno_dgaf: boolean;
  folio_turno_dgaf: string | null;
  fecha_turno_dgaf: string | null;
  respuestaA: OficioRespuesta | null;
  documentos: Documento[];
  permissions: Permission[];
  expediente: {
    id: number;
    numero_expediente: string;
    areas: Area[];
    oficios: OficioEnHistorial[]; // <-- Se añade la relación para el historial
  } | null;
}

const props = defineProps<{
  oficio: Oficio;
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Oficios', href: '/oficios' },
  { title: props.oficio.expediente?.numero_expediente || 'Detalle', href: `/expedientes/${props.oficio.expediente?.id}` },
  { title: props.oficio.asunto },
];

</script>

<template>
    <Head :title="`Oficio: ${oficio.asunto}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="space-y-8">
                
                <OficioHeader :oficio="oficio" />

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-2 space-y-6">
                        
                        <DetallesEntrada v-if="oficio.tipo === 'entrada'" :oficio="oficio" />
                        <DetallesSalida v-if="oficio.tipo === 'salida'" :oficio="oficio" />
                        
                        <!-- Sección de Descripción -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Descripción</h3>
                            <p v-if="oficio.descripcion" class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ oficio.descripcion }}</p>
                            <p v-else class="text-sm text-gray-500 dark:text-gray-400">No se proporcionó una descripción.</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                         <ListaDocumentos :oficio="oficio" />
                         <AsignacionesCard :oficio="oficio" />
                    </div>
                </div>

                <!-- Historial del Expediente -->
                <HistorialExpediente :expediente="oficio.expediente" :oficio-actual-id="oficio.id" />
                
            </div>
        </div>
    </AppLayout>
</template>