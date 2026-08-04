fetch('https://back-dli7.onrender.com/api/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email: 'erkt39966@gmail.com', password: '12345678' })
})
.then(res => res.json())
.then(data => console.log(data));