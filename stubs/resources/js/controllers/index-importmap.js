import HelloController from "controllers/hello_controller"

export default (Stimulus) => {
    Stimulus.register("hello", HelloController)
};
