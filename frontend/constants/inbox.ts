import { Conversation } from "@/types/inbox";

export const API_BASE_URL = "http://localhost:8000/api";
export const TOKEN_KEY = "miinbox_token";
export const ACCENT = "#5340c4";

export const AVATAR_COLORS: Record<string, { bg: string; color: string }> = {
  AL: { bg: "#EEEDFE", color: "#3C3489" },
  BO: { bg: "#E1F5EE", color: "#085041" },
  GR: { bg: "#FAECE7", color: "#712B13" },
};

export const DEFAULT_AVATAR_COLOR = { bg: "#F1EFE8", color: "#444441" };

export const CONVERSATIONS: Conversation[] = [
  {
    id: "alice",
    name: "Alice López",
    initials: "AL",
    preview: "Re: propuesta Q2",
    time: "10:32",
    unread: 2,
    messages: [
      {
        id: 1,
        subject: "Re: propuesta Q2",
        from: "Alice López",
        email: "alice@ejemplo.com",
        time: "Hoy, 10:32",
        body: `Hola,

Revisé la propuesta del Q2 y me parece que los números están bien. Solo me gustaría ajustar la sección de presupuesto antes de enviarla al cliente.

¿Podemos agendar una llamada esta tarde para revisarlo juntos?

Saludos,
Alice`,
      },
    ],
  },
  {
    id: "bob",
    name: "Bob Martínez",
    initials: "BO",
    preview: "Perfecto, lo reviso",
    time: "09:15",
    unread: 0,
    messages: [
      {
        id: 2,
        subject: "Informe semanal",
        from: "Bob Martínez",
        email: "bob@ejemplo.com",
        time: "Hoy, 09:15",
        body: `Buen día,

Te mando el resumen de la semana. Terminamos los tres módulos pendientes y el equipo está listo para el siguiente sprint.

Avísame si necesitas más detalle.

Bob`,
      },
    ],
  },
  {
    id: "group",
    name: "Chat grupal",
    initials: "GR",
    preview: "Alice: ¿cuándo es la junta?",
    time: "08:50",
    unread: 5,
    messages: [
      {
        id: 3,
        subject: "Junta de equipo – viernes",
        from: "Chat grupal",
        email: "grupo@ejemplo.com",
        time: "Hoy, 08:50",
        body: `Alice: ¿Cuándo es la junta del viernes?
Bob: La moví a las 11am
Alice: Perfecto, ¿confirmamos sala de juntas B?
Bob: Sí, ya la reservé.`,
      },
    ],
  },
];