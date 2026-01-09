<template>
    <!-- Gráfico de barras -->
    <apexchart type="bar" height="300" :options="chartOptions" :series="series" />
  </template>
  
  <script setup>
  import { ref, watch } from 'vue';
  import ApexChart from 'vue3-apexcharts';
  
  const props = defineProps({
    // Array de objetos [{ label: "2025-03", value: 3150 }, ...]
    gastosPorMes: {
      type: Array,
      default: () => [],
    },
  });
  
  // Serie inicial
  const series = ref([
    {
      name: 'Gastos',
      data: props.gastosPorMes.map(item => item.value),
    },
  ]);
  
  const chartOptions = ref({
    chart: {
      id: 'chart-gastos-mes',
    },
    plotOptions: {
      bar: {
        distributed: true,
        columnWidth: '50%',
      },
    },
    xaxis: {
      categories: props.gastosPorMes.map(item => item.label),
    },
    title: {
      text: 'Gastos totales (últimos 6 meses)',
      align: 'left',
    },
    yaxis: {
      labels: {
        formatter: val => val.toFixed(2),
      },
    },
  });
  
  // Cuando cambie "gastosPorMes", actualizamos
  watch(
    () => props.gastosPorMes,
    (newVal) => {
      series.value = [
        {
          name: 'Gastos',
          data: newVal.map(item => item.value),
        },
      ];
      chartOptions.value.xaxis.categories = newVal.map(item => item.label);
    },
    { immediate: true }
  );
  </script>
  