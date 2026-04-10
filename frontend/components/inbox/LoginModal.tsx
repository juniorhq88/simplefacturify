"use client";

import { useState, FormEvent } from "react";
import { API_BASE_URL } from "@/constants/inbox";
import { LoginResponse } from "@/types/inbox";
import styles from "@/styles/inbox.styles";

interface LoginModalProps {
  onLogin: (token: string) => void;
}

export default function LoginModal({ onLogin }: LoginModalProps) {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  async function handleSubmit(e: FormEvent<HTMLFormElement>): Promise<void> {
    e.preventDefault();

    if (!email || !password) {
      setError("Completa todos los campos.");
      return;
    }

    setLoading(true);
    setError("");

    try {
      const res = await fetch(`${API_BASE_URL}/login`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({ email, password }),
      });

      const data: LoginResponse = await res.json();
      const token = data.token ?? data.access_token;

      if (res.ok && token) {
        onLogin(token);
      } else {
        setError(data.message ?? `Error ${res.status}: Credenciales incorrectas.`);
      }
    } catch (err) {
      const msg = err instanceof Error ? err.message : "Error desconocido";
      setError(`No se pudo conectar al servidor. (${msg})`);
    } finally {
      setLoading(false);
    }
  }

  return (
    <div style={styles.overlay}>
      <div style={styles.loginCard}>
        <div style={styles.logo}>
          <div style={styles.logoIcon} aria-hidden="true">✉</div>
          <span style={styles.logoText}>MiInbox</span>
        </div>

        <h1 style={styles.loginTitle}>Iniciar sesión</h1>
        <p style={styles.loginSub}>Conecta con tu cuenta</p>

        <form onSubmit={handleSubmit} noValidate>
          <div style={styles.field}>
            <label htmlFor="email" style={styles.label}>
              Correo electrónico
            </label>
            <input
              id="email"
              style={styles.input}
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="tu@correo.com"
              autoFocus
              autoComplete="email"
            />
          </div>

          <div style={styles.field}>
            <label htmlFor="password" style={styles.label}>
              Contraseña
            </label>
            <input
              id="password"
              style={styles.input}
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="••••••••"
              autoComplete="current-password"
            />
          </div>

          {error && (
            <p role="alert" style={styles.errorText}>
              {error}
            </p>
          )}

          <button type="submit" style={styles.btnPrimary} disabled={loading}>
            {loading ? "Conectando..." : "Entrar"}
          </button>
        </form>
      </div>
    </div>
  );
}