"use client";

import { useState } from "react";
import { useAuth } from "@/hooks/useAuth";
import { CONVERSATIONS } from "@/constants/inbox";
import LoginModal from "@/components/inbox/LoginModal";
import Sidebar from "@/components/inbox/Sidebar";
import ReadingPane from "@/components/inbox/ReadingPane";
import styles from "@/styles/inbox.styles";

export default function InboxPage() {
  const { token, login, logout } = useAuth();
  const [activeId, setActiveId] = useState("alice");
  const [search, setSearch] = useState("");

  const filtered = CONVERSATIONS.filter(
    (c) =>
      c.name.toLowerCase().includes(search.toLowerCase()) ||
      c.preview.toLowerCase().includes(search.toLowerCase())
  );

  const activeConversation = filtered.find((c) => c.id === activeId) ?? null;

  if (!token) return <LoginModal onLogin={login} />;

  return (
    <div style={styles.app}>
      {/* Topbar */}
      <header style={styles.topbar}>
        <div style={styles.logo}>
          <div style={styles.logoIcon} aria-hidden="true">✉</div>
          <span style={styles.logoText}>MiInbox</span>
        </div>

        <label htmlFor="search" style={{ display: "none" }}>
          Buscar conversaciones
        </label>
        <input
          id="search"
          style={styles.searchInput}
          type="search"
          placeholder="Buscar…"
          value={search}
          onChange={(e) => setSearch(e.target.value)}
        />

        <div style={{ display: "flex", gap: 8, marginLeft: "auto" }}>
          <button style={styles.btnOutline}>+ Nuevo mensaje</button>
          <button
            style={{ ...styles.btnOutline, color: "#c0392b" }}
            onClick={logout}
          >
            Salir
          </button>
        </div>
      </header>

      {/* Main */}
      <main style={styles.main}>
        <Sidebar
          conversations={filtered}
          activeId={activeId}
          onSelect={setActiveId}
        />
        <ReadingPane conversation={activeConversation} />
      </main>
    </div>
  );
}