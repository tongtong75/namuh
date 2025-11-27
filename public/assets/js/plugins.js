if (
  document.querySelector("[toast-list]") ||
  document.querySelector("[data-choices]") ||
  document.querySelector("[data-provider]")
) {
  const script = document.createElement("script");
  script.src = "https://cdn.jsdelivr.net/npm/toastify-js";
  document.head.appendChild(script);
}