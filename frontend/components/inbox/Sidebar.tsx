"use client";

import { Conversation } from "@/types/inbox";
import { AVATAR_COLORS, DEFAULT_AVATAR_COLOR } from "@/constants/inbox";
import styles from "@/styles/inbox.styles";

interface SidebarProps {
  conversations: Conversation[];
  activeId: string;
  onSelect: (id: string) => void;
}

export default function Sidebar({ conversations, activeId, onSelect }: SidebarProps) {
  return (
    <aside style={styles.sidebar}>
      <p style={styles.sidebarHeader}>Conversaciones</p>

      {conversations.map((c) => {
        const avatarColor = AVATAR_COLORS[c.initials] ?? DEFAULT_AVATAR_COLOR;
        const isActive = activeId === c.id;

        return (
          <div
            key={c.id}
            role="button"
            tabIndex={0}
            style={{
              ...styles.convoItem,
              ...(isActive ? styles.convoItemActive : {}),
            }}
            onClick={() => onSelect(c.id)}
            onKeyDown={(e) => e.key === "Enter" && onSelect(c.id)}
            aria-current={isActive ? "true" : undefined}
          >
            <div
              style={{ ...styles.avatar, background: avatarColor.bg, color: avatarColor.color }}
              aria-hidden="true"
            >
              {c.initials}
            </div>

            <div style={styles.convoInfo}>
              <p style={styles.convoName}>{c.name}</p>
              <p style={styles.convoPreview}>{c.preview}</p>
            </div>

            {c.unread > 0 && (
              <span style={styles.badge} aria-label={`${c.unread} mensajes sin leer`}>
                {c.unread}
              </span>
            )}
          </div>
        );
      })}
    </aside>
  );
}