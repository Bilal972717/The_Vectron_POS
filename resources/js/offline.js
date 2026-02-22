import { openDB } from "idb";

const DB_NAME = "qpos-offline";
const STORE_NAME = "sync-queue";

export async function initDB() {
  return openDB(DB_NAME, 1, {
    upgrade(db) {
      if (!db.objectStoreNames.contains(STORE_NAME)) {
        db.createObjectStore(STORE_NAME, { keyPath: "id", autoIncrement: true });
      }
    }
  });
}

export async function addToQueue(endpoint, payload) {
  const db = await initDB();
  await db.add(STORE_NAME, { endpoint, payload });
}

export async function getQueue() {
  const db = await initDB();
  return db.getAll(STORE_NAME);
}

export async function clearQueue() {
  const db = await initDB();
  const tx = db.transaction(STORE_NAME, "readwrite");
  await tx.store.clear();
  await tx.done;
}