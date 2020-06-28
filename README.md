# Angular9
## Get started
# FrontApi

This project was generated with [Angular CLI](https://github.com/angular/angular-cli) version 9.0.0.
### Clone the repo

```shell
* git clone https://gitlab.com/grepruby/projects/client/urgent-fury/front-api.git
* cd front-api
```

### Install npm packages

Install the `npm` packages described in the `package.json` and verify that it works:

```shell
* npm install
* npm start
```

The `npm start` command builds (compiles TypeScript), and watches for changes to the source files, and runs `server` on port `4200`.

Shut it down manually with `Ctrl-C`.

#### Make sure to install angular v9 as @angular/cli by

```shell
* npm install -g @angular/cli@9.0.0
```

#### npm scripts

These are the most useful commands defined in `package.json`:

* `npm start` - runs the TypeScript compiler, asset copier, and a server at the same time, all three in "watch mode".
* `npm run build` - runs the TypeScript compiler and asset copier once.
* `npm run build:prod` - runs the TypeScript compiler and asset copier in "production mode"; when changes occur to source files, they will be recompiled or copied into `dist/`.
* `npm run build:staging` - runs the TypeScript compiler and asset copier in "staging mode"; when changes occur to source files, they will be recompiled or copied into `dist/`.

## Development server

Run `ng serve` for a dev server. Navigate to `http://localhost:4200/`. The app will automatically reload if you change any of the source files.

## Code scaffolding

Run `ng generate component component-name` to generate a new component. You can also use `ng generate directive|pipe|service|class|guard|interface|enum|module`.

## Integrated pages / functionalties in this boilerplate

* Login
* Signup
* Forgot password
* Reset Password
* Verify Account
* Home Page
* Auth Guard
* Interceptor
* My-Profile
* Lobby

## Build

Run `ng build` to build the project. The build artifacts will be stored in the `dist/` directory. Use the `--prod` flag for a production build.

## Further help

To get more help on the Angular CLI use `ng help` or go check out the [Angular CLI README](https://github.com/angular/angular-cli/blob/master/README.md).
