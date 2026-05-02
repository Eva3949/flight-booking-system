document.addEventListener("DOMContentLoaded", () => {
  // Initialize date inputs with today's date as minimum
  const dateInputs = document.querySelectorAll('input[type="date"]')
  const today = new Date().toISOString().split("T")[0]

  dateInputs.forEach((input) => {
    if (!input.min) {
      input.min = today
    }

    // If no date is selected, set today as default
    if (!input.value) {
      input.value = today
    }
  })

  // Validate source and destination are not the same
  const searchForms = document.querySelectorAll('form[action="search.php"]')

  searchForms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      const source = form.querySelector("#source").value
      const destination = form.querySelector("#destination").value

      if (source === destination && source !== "") {
        e.preventDefault()
        alert("Source and destination airports cannot be the same.")
      }
    })
  })

  // Mobile menu toggle
  const menuToggle = document.createElement("div")
  menuToggle.className = "menu-toggle"
  menuToggle.innerHTML = '<i class="fas fa-bars"></i>'

  const header = document.querySelector("header .container")
  const nav = document.querySelector("header nav")

  if (header && nav) {
    header.insertBefore(menuToggle, nav)

    menuToggle.addEventListener("click", () => {
      nav.classList.toggle("active")

      if (nav.classList.contains("active")) {
        menuToggle.innerHTML = '<i class="fas fa-times"></i>'
      } else {
        menuToggle.innerHTML = '<i class="fas fa-bars"></i>'
      }
    })
  }

  // Add responsive styles for mobile menu
  const style = document.createElement("style")
  style.textContent = `
        @media (max-width: 768px) {
            header .container {
                flex-wrap: wrap;
            }
            
            .menu-toggle {
                display: block;
                font-size: 1.5rem;
                cursor: pointer;
                color: #fff;
            }
            
            header nav {
                display: none;
                width: 100%;
                margin-top: 1rem;
            }
            
            header nav.active {
                display: block;
            }
            
            header nav ul {
                flex-direction: column;
            }
            
            header nav ul li {
                margin: 0.5rem 0;
            }
        }
    `

  document.head.appendChild(style)
})
