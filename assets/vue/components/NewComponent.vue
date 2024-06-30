<template>
    <div>Hello {{ name }}!</div>
    <div>ddddd  {{ name }}!</div>
    <div>Hello {{ name }}!</div>
    <div>Hello {{ name }}!</div>
</template>

<script setup>
    defineProps({
        name: String
    });
</script>
