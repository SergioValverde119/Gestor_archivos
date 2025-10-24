import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href?: string; // <-- CORRECCIÓN: Se mantiene como opcional
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface SimpleUser {
  id: number;
  name: string;
}

export interface Area { 
    id: number; 
    nombre: string; 
}

export interface Documento { 
    id: number; 
    nombre_documento: string; 
    ruta_almacenamiento: string; 
    rol_documento: 'principal' | 'anexo'; 
}

export interface Permission { 
    user: SimpleUser; 
    permission_level: 'editor' | 'visualizador';
}

export interface Expediente { 
    id: number; 
    numero_expediente: string; 
    areas: Area[]; 
}

export interface Oficio {
  id: number;
  tipo: 'entrada' | 'salida';
  folio_externo: string | null;
  folio_salida: string | null;
  folio_interno: string | null;
  remitente: string | null;
  destinatario: string | null;
  asunto: string | null;
  descripcion: string | null;
  status: string;
  prioridad: string;
  resolucion: string | null; 
  fecha_recepcion: string | null;
  fecha_limite: string | null;
  tiene_turno_dgaf: boolean;
  folio_turno_dgaf: string | null;
  fecha_turno_dgaf: string | null;
  expediente: ExpedienteConOficios | null; // <-- CORRECCIÓN: Debe usar el tipo completo
  recibidoPor: SimpleUser | null; // <-- Se usa el tipo simple
  created_at: string;
  documentos: Documento[];
  permissions: Permission[];
  respuestaA: { id: number; folio_interno: string; } | null;
}

// --- NUEVO: Tipos Específicos de Página ---

export interface OficioEnHistorial {
  id: number;
  tipo: 'entrada' | 'salida';
  asunto: string;
  created_at: string;
  folio_externo: string | null;
  folio_salida: string | null;
  recibidoPor: SimpleUser | null;
}

// --- NUEVO: Interfaz para el expediente que incluye su historial ---
export interface ExpedienteConOficios extends Expediente {
  oficios: OficioEnHistorial[];
}

export interface PaginationLink { 
    url: string | null; 
    label: string; 
    active: boolean; 
}

export interface PaginatedOficios { 
    data: Oficio[]; 
    links: PaginationLink[]; 
}

export interface OficioFilters {
    search?: string;
    sort?: string | null;
    direction?: 'asc' | 'desc' | null;
    tiene_turno_dgaf?: string | null;
    tipo?: 'entrada' | 'salida' | null;
    per_page?: number;
    date_from?: string | null;
    date_to?: string | null;
    // --- CORRECCIÓN: Se añaden los campos que faltaban ---
    recepcion_from?: string | null;
    recepcion_to?: string | null;
    limite_from?: string | null;
    limite_to?: string | null;
    area_ids?: number[];
}

export interface SearchableOficio {
  id: number;
  folio_interno: string | null;
  folio_externo: string | null;
  folio_salida: string | null;
  asunto: string;
}


