"use client";

import { useState, useCallback } from "react";
import { Conversation, Message } from "@/types/inbox";
import { useSendMessage } from "@/hooks/useSendMessage";
import styles from "@/styles/inbox.styles";

interface ReadingPaneProps {
  conversation: Conversation | null;
  onMessageSent?: (threadId: string, message: Message) => void;
  isComposing?: boolean;
  onComposeClose?: () => void;
}

export default function ReadingPane({ conversation, onMessageSent, isComposing, onComposeClose }: ReadingPaneProps) {
  const [reply, setReply] = useState("");
  const [sent, setSent] = useState(false);
  const [to, setTo] = useState("");
  const [subject, setSubject] = useState("");
  const { send } = useSendMessage();

  const handleSend = useCallback(async (): Promise<void> => {
    if (!conversation || !reply.trim()) return;

    try {
      await send({ threadId: conversation.id, body: reply });
      
      if (onMessageSent) {
        const newMessage: Message = {
          id: Date.now(),
          subject: conversation.messages[0]?.subject || "Re: " + conversation.name,
          from: "Tú",
          email: "",
          time: "Ahora",
          body: reply,
        };
        onMessageSent(conversation.id, newMessage);
      }
    } catch {
      // TODO: mostrar toast de error
    }

    setReply("");
    setSent(true);
    setTimeout(() => setSent(false), 3000);
  }, [conversation, reply, send, onMessageSent]);

  if (!conversation) {
    if (isComposing) {
      const handleNewSend = async (): Promise<void> => {
        if (!to.trim() || !subject.trim() || !reply.trim()) return;
        try {
          await send({ to, subject, body: reply });
        } catch {
          // TODO: mostrar toast de error
        }
        setReply("");
        setTo("");
        setSubject("");
        setSent(true);
        if (onComposeClose) onComposeClose();
        setTimeout(() => setSent(false), 3000);
      };

      return (
        <div style={styles.readingPane}>
          <div style={styles.composeHeader}>
            <h2 style={styles.msgSubject}>Nuevo mensaje</h2>
          </div>
          <div style={styles.composeForm}>
            <div style={styles.composeField}>
              <label htmlFor="to-input" style={styles.label}>Para:</label>
              <input
                id="to-input"
                style={styles.input}
                type="email"
                placeholder="destinatario@ejemplo.com"
                value={to}
                onChange={(e) => setTo(e.target.value)}
              />
            </div>
            <div style={styles.composeField}>
              <label htmlFor="subject-input" style={styles.label}>Asunto:</label>
              <input
                id="subject-input"
                style={styles.input}
                type="text"
                placeholder="Asunto del mensaje"
                value={subject}
                onChange={(e) => setSubject(e.target.value)}
              />
            </div>
            <div style={styles.composeField}>
              <label htmlFor="compose-body" style={{ display: "none" }}>Mensaje</label>
              <textarea
                id="compose-body"
                style={styles.replyTextarea}
                placeholder="Escribir mensaje..."
                value={reply}
                onChange={(e) => setReply(e.target.value)}
                rows={10}
              />
            </div>
            <div style={styles.replyFooter}>
              {sent && (
                <span role="status" style={{ fontSize: 13, color: "#1D9E75" }}>
                  ¡Enviado!
                </span>
              )}
              <button style={styles.btnSend} onClick={handleNewSend} disabled={!to.trim() || !subject.trim() || !reply.trim()}>
                Enviar
              </button>
            </div>
          </div>
        </div>
      );
    }

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