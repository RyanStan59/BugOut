document.getElementById("bugForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const formData = new FormData(e.target);

  const res = await fetch("../api/create_bug.php", {
    method: "POST",
    body: formData
  });

  const data = await res.json();

  if (data.status === "success") {
    alert("Bug submitted!");
    e.target.reset();
  } else {
    alert("Error submitting bug.");
  }
});