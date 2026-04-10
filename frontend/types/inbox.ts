export interface Message {
  id: number;
  subject: string;
  from: string;
  email: string;
  time: string;
  body: string;
}

export interface Conversation {
  id: string;
  name: string;
  initials: string;
  preview: string;
  time: string;
  unread: number;
  messages: Message[];
}

export interface User {
  email: string;
  name?: string;
}

export interface LoginResponse {
  token?: string;
  access_token?: string;
  message?: string;
  user?: User;
}

export interface SendMessagePayload {
  to: string;
  subject: string;
  body: string;
}