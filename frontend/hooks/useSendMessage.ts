"use client";

import { useCallback } from "react";
import { API_BASE_URL, TOKEN_KEY } from "@/constants/inbox";
import { SendMessagePayload } from "@/types/inbox";

export function useSendMessage() {
  const send = useCallback(async (payload: SendMessagePayload): Promise<void> => {
    const token = localStorage.getItem(TOKEN_KEY);

    await fetch(`${API_BASE_URL}/messages`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        Authorization: `Bearer ${token ?? ""}`,
      },
      body: JSON.stringify(payload),
    });
  }, []);

  return { send };
}