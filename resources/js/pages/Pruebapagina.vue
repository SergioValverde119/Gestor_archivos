<template>
  <div class="container">
    <h1>{{ titulo }}</h1>
    <p>{{ mensaje }}</p>
    <a href="/">Volver a la página principal</a>
  </div>
</template>

<script setup>
// Aquí le dices a la vista qué datos espera recibir de Laravel
defineProps({
  titulo: String,
  mensaje: String,
});
</script>

<style scoped>
.container {
  max-width: 800px;
  margin: 50px auto;
  padding: 20px;
  text-align: center;
  border-radius: 8px;
  background-color: #f0f4f8;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
h1 {
  color: #2c3e50;
}
p {
  color: #34495e;
  font-size: 1.2rem;
}
</style>