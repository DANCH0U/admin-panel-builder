import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Badge } from "./Badge.vue"

export const badgeVariants = cva(
  "inline-flex gap-1 items-center rounded-full border bg-transparent px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2",
  {
    variants: {
      variant: {
        default:
          "border-primary/40 text-primary",
        secondary:
          "border-border text-muted-foreground",
        destructive:
          "border-destructive/50 text-destructive",
        outline:
          "border-border text-foreground",
        success:
          "border-emerald-500/60 text-emerald-700 dark:text-emerald-400",
        warning:
          "border-amber-500/60 text-amber-700 dark:text-amber-400",
        info:
          "border-sky-500/60 text-sky-700 dark:text-sky-400",
        danger:
          "border-destructive/50 text-destructive",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  },
)

export type BadgeVariants = VariantProps<typeof badgeVariants>
