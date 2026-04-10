"use client";

import { useState, useCallback } from "react";
import { Conversation } from "@/types/inbox";
import { useSendMessage } from "@/hooks/useSendMessage";
import styles from "@/styles/inbox.styles";

interface ReadingPaneProps {
  conversation: Conversation | null;
}

export default function ReadingPane({ conversation }: ReadingPaneProps) {
  const [reply, setReply] = useState("");
  const [sent, setSent] = useState(false);
  const { send } = useSendMessage();

  const handleSend = useCallback(async (): Promise<void> => {
    if (!conversation || !reply.trim()) return;

    const msg = conversation.messages[0];

    try {
      await send({ to: msg.email, subject: msg.subject, body: reply });
    } catch {
      // TODO: mostrar toast de error
    }

    setReply("");
    setSent(true);
    setTimeout(() => setSent(false), 3000);
  }, [conversation, reply, send]);

  if (!conversation) {
    return (
      <div style={{ ...styles.readingPane, alignItems: "center", justifyContent: "center" }}>
        <p style={{ color: "#888" }}>Selecciona una conversación</p>
      </div>
    );
  }

  const msg = conversation.messages[0];
  const senderInitials = msg.from.slice(0, 2).toUpperCase();

  return (
    <div style={styles.readingPane}>
      {/* Header */}
      <div style={styles.msgHeader}>
        <h2 style={styles.msgSubject}>{msg.subject}</h2>
        <div style={styles.msgMeta}>
          <div style={styles.senderAvatar} aria-hidden="true">
            {senderInitials}
          </div>
          <span style={styles.msgFrom}>
            {msg.from} &lt;{msg.email}&gt;
          </span>
          <span style={styles.msgTime}>{msg.time}</span>
        </div>
      </div>

      {/* Body */}
      <div style={styles.msgBody}>
        {msg.body.split("\n").map((line, i) => (
          <span key={i}>
            {line}
            <br />
          </span>
        ))}
      </div>

      {/* Reply */}
      <div style={styles.replyArea}>
        <label htmlFor="reply-textarea" style={{ display: "none" }}>
          Escribir respuesta
        </label>
        <textarea
          id="reply-textarea"
          style={styles.replyTextarea}
          placeholder="Escribir respuesta..."
          value={reply}
          onChange={(e) => setReply(e.target.value)}
          rows={3}
        />
        <div style={styles.replyFooter}>
          {sent && (
            <span role="status" style={{ fontSize: 13, color: "#1D9E75" }}>
              ¡Enviado!
            </span>
          )}
          <button style={styles.btnSend} onClick={handleSend} disabled={!reply.trim()}>
            Enviar
          </button>
        </div>
      </div>
    </div>
  );
}