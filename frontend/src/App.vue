<template>
    <app-layout>
        <home-view />
    </app-layout>
</template>

<script setup>
import { AppLayout } from "@/layouts";
import { HomeView } from "@/views";
</script>

<style lang="scss">
@import "@/assets/scss/app.scss";
</style>

<!--<style lang="scss">-->
<!--@import "@/assets/scss/app.scss";-->

<!--#app {-->
<!--  position: fixed;-->
<!--  width: 100%;-->
<!--  height: 100%;-->
<!--  background-color: $blue-600;-->
<!--  top: 0;-->
<!--  left: 0;-->
<!--}-->

<!--.main {-->
<!--  position: absolute;-->
<!--  top: 50%;-->
<!--  left: 50%;-->
<!--  transform: translate(-50%, -50%);-->
<!--  color: $white-900;-->

<!--  img {-->
<!--    display: block;-->
<!--    margin: 0 auto;-->
<!--  }-->

<!--  h1 {-->
<!--    @include m-s36-h21;-->
<!--    text-align: center;-->
<!--    margin-bottom: 0;-->
<!--  }-->

<!--  p {-->
<!--    font-size: 20px;-->
<!--    line-height: 30px;-->
<!--    text-align: center;-->
<!--  }-->

<!--  b {-->
<!--    font-size: 1.2em;-->
<!--  }-->
<!--}-->
<!--</style>-->