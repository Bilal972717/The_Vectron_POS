// import './bootstrap';
import React from 'react'
import Pos from "./components/Pos";
import Purchase from './components/Purchase/Purchase';
import { createRoot } from 'react-dom/client';

import { getQueue, clearQueue } from "./offline.js";

// Register Service Worker
if ("serviceWorker" in navigator) {
  window.addEventListener("load", () => {
    navigator.serviceWorker.register("/sw.js")
      .then(reg => console.log("Service Worker registered:", reg.scope))
      .catch(err => console.log("Service Worker failed:", err));
  });
}

// Sync queued offline actions when back online
window.addEventListener("online", async () => {
  const queued = await getQueue();
  for (let item of queued) {
    try {
      await axios.post(item.endpoint, item.payload);
    } catch (err) {
      console.error("Failed to sync:", err);
    }
  }
  await clearQueue();
  if (queued.length > 0) alert("Offline data synced!");
});

// export default function app() {
//   return (
//     <Pos />
//   )
// }

// Check for the 'cart' element and render the 'cart' component using createRoot
if (document.getElementById("cart")) {
    const cartRoot = createRoot(document.getElementById("cart"));
    cartRoot.render(<Pos />);
}

// Check for the 'purchase' element and render the 'Purchase' component using createRoot
if (document.getElementById("purchase")) {
    const purchaseRoot = createRoot(
        document.getElementById("purchase")
    );
    purchaseRoot.render(<Purchase />);
}

